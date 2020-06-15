<?php

namespace App\Library;

use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;

class FunctionsLibrary {
	private $db;
	private $logger;
	private $settings;
	private $mailer;
	public $data   = [];
	public $errors = [];

	public function __construct( $db, LoggerInterface $logger, $settings, $mailer ) {
		$this->db       = $db;
		$this->logger   = $logger;
		$this->settings = $settings;
		$this->mailer   = $mailer;
	}

	public function create_id() {
		if ( function_exists( 'com_create_guid' ) === true )
			return trim( com_create_guid(), '{}' );
		$data        = openssl_random_pseudo_bytes( 16 );
		$data[6]     = chr( ord( $data[6] ) & 0x0f | 0x40 ); // set version to 0100
		$data[8]     = chr( ord( $data[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10
		$id = vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
		$this->logger->info( 'Created ID: ' . $id );
		return $id;
	}

	public function encrypt_decrypt( $action, $string ) {
		$output         = false;
		$encrypt_method = 'AES-256-CBC';
		$secret_key     = $this->settings['SALT'];
		$secret_iv      = $this->settings['SALT_KEY'];
		// hash
		$key = hash( 'sha256', $secret_key );
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
		if ( 'encrypt' === $action ) {
			$output = openssl_encrypt( $string, $encrypt_method, $key, 0, $iv );
			$output = base64_encode( $output );
		} else if ( 'decrypt' === $action ) {
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}
		return $output;
	}

	public function record_exists( $table, $field, $value ) {
		$records = $this->db->where( $field, $value )->get( $table );
		$this->logger->info( $this->db->getLastQuery() );
		return $this->db->count;
	}

	public function get_userid( $request ) {
		$decoded = $request->getAttribute( 'jwt' );
		return $decoded[0]->user_id;
	}

	public function create_jwt( $payload ) {
		try {
			// JWT vars.
			$jwt_secret     = $this->settings['jwt'];
			$iat            = time();
			$exp            = $iat + 8 * 60 * 60; // Set expiration at 8 hours.
			$payload['iss'] = $_SERVER['HTTP_HOST'];
			$payload['iat'] = $iat;
			$payload['exp'] = $exp;
			$jwt_token      = JWT::encode( [ $payload ], $jwt_secret, 'HS256' );
		} catch ( \Exception $e ) {
			$this->logger->error( $e->getMessage() );
		}
		return $jwt_token;
	}

	public function decode_jwt( $payload ) {
		try {
			JWT::$leeway = 60;
			$jwt_secret  = $this->settings['jwt'];
			$decoded_jwt = JWT::decode( $payload, $jwt_secret, array( 'HS256' ) );
		} catch ( \Exception $e ) {
			$this->logger->error( $e->getMessage() );
		}
		return $decoded_jwt;
	}

	/*
	public function validate_jwt( $user_id, $user_scope, $user_last_updated ) {
		// Hold any found errors.
		$errors = [];

		// Get the users record.
		$record = $this->db->where( 'user_id', $user_id )->getOne( 'users' );

		if ( $record ) {
			// Make sure still active.
			$this->logger->info( 'User Status: ' . $record['user_status'] );

			// Check status.
			if ( 0 === $record['user_status'] ) {
				$this->logger->error( 'user is inactive.' );
				array_push( $errors, 'user is inactive.' );
			}

			// Check scope.
			if ( $record['user_scope'] !== $user_scope ) {
				$this->logger->error( 'user scope mismatch.' );
				array_push( $errors, 'user scope mismatch.' );
			}

			// Check last updated.
			if ( $record['user_last_updated'] !== $user_last_updated ) {
				$this->logger->error( 'user user_last_updated mismatch.' );
				array_push( $errors, 'user user_last_updated mismatch.' );
			}
		} else {
			$this->logger->error( 'user record does not exist.' );
			array_push( $errors, 'user record does not exist.' );
		}

		if ( empty( $errors ) ) {
			$this->logger->info( 'JWT is Valid.' );
			$return = true;
		} else {
			$this->logger->error( 'JWT is Invalid.' );
			$return = false;
		}

		return $return;
	}
	*/

	public function send_verification_email( $user_id, $user_email ) {
		$this->logger->info( 'Sending Email' );
		$this->logger->info( 'EMAIL user_id: ' . $user_id );
		$this->logger->info( 'EMAIL user_email: ' . $user_email );

		$payload = [
			'user_id'    => $user_id,
			'user_email' => $user_email,
		];

		// Add the created token to the json return.
		$verification_token = $this->encrypt_decrypt( 'encrypt', $this->create_jwt( $payload ) );
		$link               = 'http://' . $this->settings['email']['verify_base_url'] . '/verify?verification_token=' . $verification_token;

		$this->logger->info( 'Adding Verification token: ' . $verification_token . ' to email.' );
		$this->logger->info( 'Link: ' . $link );

		$this->mailer->addAddress( $user_email, $user_email );

		$email_body = <<<EOT
		<!doctype html>
		<html lang="en">
		  <head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<title></title>
		  </head>
		  <body>
			  <a href="$link">Click Here</a>
			  <br/>
		  </body>
		</html>
		EOT;

		$this->mailer->Subject = 'Email Verification';
		$this->mailer->Body    = $email_body;

		if ( ! $this->mailer->send() ) {
			$this->logger->error( $this->mailer->ErrorInfo );
			$return = false;
		} else {
			// if sent, add to verification table.
			$data = array(
				'verification_user_id' => $user_id,
				'verification_token'   => $verification_token,
			);

			// Insert/Update raw data into verification table.
			// $this->db->replace( 'verification', $data );

			$this->logger->error( 'Email sent successfully' );
			$return = true;
		}
		return $return;
	}

}
