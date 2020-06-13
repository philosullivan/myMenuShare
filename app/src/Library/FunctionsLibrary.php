<?php

namespace App\Library;

use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class FunctionsLibrary {
	private $db;
	private $logger;
	private $settings;
	public $data   = [];
	public $errors = [];

	public function __construct( $db, LoggerInterface $logger, $settings ) {
		$this->db       = $db;
		$this->logger   = $logger;
		$this->settings = $settings;
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
}
