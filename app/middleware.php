<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//
$container = $app->getContainer();
$debug     = $container->get( 'settings' )['debug'];

// Debug logging middleware.
$app->add( function ( Request $request, Response $response, callable $next ) use ( $app ) {
	$this->logger->info( 'Start Route Logging' );

	$response = $next( $request, $response );
	return $response;
});
