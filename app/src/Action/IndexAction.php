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
	private $functions;
	private $db;

	public function __construct( LoggerInterface $logger, $settings, $view, $functions, $qrcode, $db ) {
		$this->logger    = $logger;
		$this->settings  = $settings;
		$this->view      = $view;
		$this->qrcode    = $qrcode;
		$this->functions = $functions;
		$this->db        = $db;
	}

	public function load_index( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading index' );
		return $this->view->render( $response, 'index.twig', [ 'return' => $this->data ] );
	}

	public function load_sign_up( Request $request, Response $response, $args ) {
		$this->logger->info( 'loading help' );
		return $this->view->render( $response, 'sign-up.twig', [ 'return' => $this->data ] );
	}

	public function post_sign_up( Request $request, Response $response, $args ) {
		$this->logger->info( 'Posting Signup' );

		// Get posted form values.
		$post_data = $request->getParsedBody();
		$bus_data  = array();
		$user_data = array();

		// Check user email.
		// $email_exists = $this->functions->record_exists( 'users', 'user_email', $post_data['user_email'] );

		// Create a UUID.
		$bus_id = $this->functions->create_id();

		// Add User ID to the array.
		$post_data['bus_id'] = $bus_id;

		// Seperate business and user data into different arrays.
		foreach ( $post_data as $key => $value ) {
			$this->logger->info( $key . ':' . $value );
		}

		// Add business here.
		/*
		try {
			if ( ! $this->db->insert( 'business', $bus_data ) ) {
				$db_error = $this->db->getLastError();
				$this->logger->error( $db_error );
				$this->data['errors']['insert_error'] = $db_error;
			} else {
				$this->logger->error( 'Record Inserted' );
			}
		} catch ( \Exception $e ) {
			$this->logger->error( $e );
			$data['data']['errors']['insert_exception'] = $e;
			return false;
		}
		*/

		// Add contact here if business was added.

		//
		// $this->logger->info( print_r( $post_data, true ) );
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
}
