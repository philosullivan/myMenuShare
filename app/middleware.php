<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//
$container = $app->getContainer();
$debug     = $container->get( 'settings' )['debug'];

// $app->add( new Slim\Csrf\Guard() );

// Debug logging middleware.
/*
$app->add( function ( Request $request, Response $response, callable $next ) use ( $app ) {
	$this->logger->info( 'Start Route Logging' );

	// Get requestors ipaddress.
	$ipaddress = $request->getAttribute( 'ip_address' );

	// Get req vars.
	$method          = $request->getMethod();
	$route           = $request->getAttribute( 'route' );
	$requested_route = $route->getPattern() || '';

	// Log some route info.
	$this->logger->info( $method . ' ' . $requested_route, [ $route->getArguments() ] );
	$this->logger->info( $ipaddress . ' ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase(), [ (string) $response->getBody() ] );
	$this->logger->info( 'End Route Logging' );
	$response = $next( $request, $response );
	return $response;
});
*/