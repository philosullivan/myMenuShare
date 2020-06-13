<?php
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

// DIC configuration.
$container = $app->getContainer();

// Twig.
$container['view'] = function ( $c ) {
	$settings = $c->get( 'settings' );
	$view     = new Slim\Views\Twig( $settings['view']['template_path'], $settings['view']['twig'] );
	$view->addExtension( new Slim\Views\TwigExtension( $c->get( 'router' ), $c->get( 'request' )->getUri() ) );
	$view->addExtension( new Twig_Extension_Debug() );
	$view->getEnvironment()->addGlobal( 'session', $_SESSION );
	return $view;
};

// Sessions.
$container['session'] = function ( $c ) {
	return new \SlimSession\Helper;
};

// Database.
$container['db'] = function ( $c ) {
	$db     = $c['settings']['database'];
	$mysqli = new MysqliDb( $db['host'], $db['user'], $db['pass'], $db['dbname'] );
	return $mysqli;
};

// Data validation.
$container['validator'] = function ( $c ) {
	$validator = new GUMP();
	return $validator;
};

// QR code.
$container['qrcode'] = function ( $c ) {
	$qrcode = new QrCode();
	return $qrcode;
};

// Functions.
$container['functions'] = function ( $c ) {
	return new App\Library\FunctionsLibrary( $c->get( 'db' ), $c->get( 'logger' ), $c->get( 'settings' ), $c->get( 'view' ) );
};

// Monolog.
$container['logger'] = function ( $c ) {
	$settings = $c->get( 'settings' );
	$logger   = new Monolog\Logger( $settings['logger']['name'] );
	$logger->pushProcessor( new Monolog\Processor\UidProcessor() );
	$logger->pushHandler( new Monolog\Handler\StreamHandler( $settings['logger']['path'], Monolog\Logger::DEBUG ) );
	return $logger;
};


$container['notFoundHandler'] = function ( $c ) {
	return function ( $request, $response ) use ( $c ) {
		$data['error_code'] = '404';
		$data['message']    = 'Page not Found';
		return $c['view']->render( $response, 'error.twig', [ 'data' => $data ] );
	};
};

$container['notAllowedHandler'] = function ( $c ) {
	return function ( $request, $response, $exception ) use ( $c ) {
		$data['error_code'] = '405';
		$data['message']    = 'Method Not Allowed';
		return $c['view']->render( $response, 'error.twig', [ 'data' => $data ] );
	};
};

$container['errorHandler'] = function ( $c ) {
	return function ( $request, $response, $exception ) use ( $c ) {
		$data['error_code'] = '500';
		$data['message']    = $exception;
		$c->get( 'logger' )->error( $exception );
		return $c['view']->render( $response, 'error.twig', [ 'data' => $data ] );
	};
};

// Other error handler.
$container['phpErrorHandler'] = function ( $c ) {
	$c->get( 'logger' )->info( 'phpErrorHandler' );
	return $c['errorHandler'];
};

// Routes.
$container[ App\Action\IndexAction::class ] = function ( $c ) {
	return new App\Action\IndexAction( $c->get( 'logger' ), $c->get( 'settings' ), $c->get( 'view' ), $c->get( 'functions' ), $c->get( 'qrcode' ), $c->get( 'db' ) );
};
