<?php
namespace App\Action;

use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

/*
*
*
*/
class IndexAction {
	private $logger;
	private $settings;
	private $view;
	private $qrcode;
	public $data = [];
	public $errors = [];
	private $functions;
	private $db;

	public function __construct( LoggerInterface $logger, $settings, $view, $functions, $qrcode, $db ) {
		$this->logger    = $logger;
		$this->settings  = $settings;
		$this->view      = $view;
		$this->qrcode    = $qrcode;
		$this->functions = $functions;
		$this->db        = $db;
		$this->data      = $data;
		$this->errors    = $errors;
	}

	public function load_index( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading index' );
		return $this->view->render( $response, 'index.twig', [ 'return' => $this->data ] );
	}

	public function load_sign_up( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading Signup form' );
		$name                     = $request->getAttribute( 'csrf_name' );
		$value                    = $request->getAttribute( 'csrf_value' );
		$this->data['csrf_name']  = $name;
		$this->data['csrf_value'] = $value;
		return $this->view->render( $response, 'sign-up.twig', [ 'return' => $this->data ] );
	}

	public function post_sign_up( Request $request, Response $response, $args ) {
		$this->logger->info( 'Creating account' );

		// Get posted form values.
		$post_data = $request->getParsedBody();

		// Original password.
		$orig_password = $post_data['user_password'];

		// Debug.
		foreach ( $post_data as $key => $value ) {
			$this->logger->info( $key . ':' . $value );
		}

		// Check user email.
		$email_exists = $this->functions->record_exists( 'users', 'user_email', $post_data['user_email'] );

		if ( $post_data['user_password'] !== $post_data['user_password_confirm'] ) {
			$this->add_error( 'Passwords do not match' );
			$this->logger->error( 'Passwords do not match' );
		}

		if ( $email_exists ) {
			$this->add_error( 'User email exists' );
			$this->logger->error( $post_data['user_email'] . ' exists.' );
			//
			$this->data['user'] = $post_data;
		} else {
			// Create a user id (UUID).
			$user_id = $this->functions->create_id();

			// Add User ID to the array.
			$post_data['user_id'] = $user_id;

			// hash the password.
			$user_password              = $post_data['user_password'];
			$post_data['user_password'] = $this->functions->encrypt_decrypt( 'encrypt', $user_password );

			// Remove the user password confirm item.
			unset( $post_data['user_password_confirm'] );
			unset( $post_data['csrf_name'] );
			unset( $post_data['csrf_value'] );

			// Add user.
			if ( empty( $this->errors ) ) {
				try {
					if ( ! $this->db->insert( 'users', $post_data ) ) {
						$db_error = $this->db->getLastError();
						$this->logger->error( $db_error );
						$this->add_error( $db_error );
					} else {
						$this->logger->info( 'Record Inserted' );
						$this->data['result'] = 'success';
						$this->functions->send_verification_email( $user_id, $post_data['user_email'] );
					}
				} catch ( \Exception $e ) {
					$this->add_error( $e );
					$this->logger->error( $e );
					return false;
				}
			} else {
				$this->data['user'] = $post_data;
			}
		}

		// Add any errors to the return.
		if ( $this->errors ) {
			$this->data['errors'] = $this->errors;
		}

		return $this->view->render( $response, 'sign-up.twig', [ 'return' => $this->data ] );
	}

	public function load_log_in( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading help' );
		return $this->view->render( $response, 'log-in.twig', [ 'return' => $this->data ] );
	}

	public function load_contact_us( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading help' );
		return $this->view->render( $response, 'contact-us.twig', [ 'return' => $this->data ] );
	}

	public function load_help( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading help' );
		return $this->view->render( $response, 'help.twig', [ 'return' => $this->data ] );
	}

	public function load_about_us( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading about' );
		return $this->view->render( $response, 'about.twig', [ 'return' => $this->data ] );
	}

	public function load_test( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading test' );

		$this->qrcode->setSize( 500 );
		$this->qrcode->setText( 'https://phil-osullivan.com' );
		$this->qrcode->setWriterByName( 'png' );
		$this->qrcode->setEncoding( 'UTF-8' );
		$this->qrcode->setErrorCorrectionLevel( ErrorCorrectionLevel::HIGH() );
		$this->qrcode->setValidateResult( false );
		$this->qrcode->setRoundBlockSize( true );
		$this->qrcode->setMargin( 10 );
		$this->qrcode->setWriterOptions( [ 'exclude_xml_declaration' => true] );

		// Directly output the QR code
		// header('Content-Type: '.$this->qrcode->getContentType());
		// echo $this->qrcode->writeString();

		// Save it to a file
		$this->qrcode->writeFile( $this->settings['storage'] . '/qrcode.png' );

		// Generate a data URI to include image data inline (i.e. inside an <img> tag)
		// $dataUri = $this->qrcode->writeDataUri();

		$this->data['image'] = $this->qrcode->writeDataUri();

		return $this->view->render( $response, 'test.twig', [ 'return' => $this->data ] );
	}

	public function add_error( $error ) {
		$error_count               = count( $this->errors );
		$error_id                  = $error_count + 1;
		$this->errors[ $error_id ] = $error;
	}
}
