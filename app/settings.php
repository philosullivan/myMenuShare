<?php
	// Split logs by date.
	$dt       = new DateTime();
	$log_name = $dt->format( 'Y_m_d' );

return [
	'settings' => [
		'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails'               => true,
		'addContentLengthHeader'            => true,
		'debug'                             => true,
		'env'                               => getenv( 'ENV' ),
		'auth_type'                         => getenv( 'AUTH_TYPE' ),
		'version'                           => getenv( 'VERSION' ),
		'salt'                              => getenv( 'SALT' ),
		'storage'                           => __DIR__ . '/storage',
		'view'                              => [
			'template_path' => __DIR__ . '/templates',
			'twig'        => [
			'cache'       => true,
			'cache'       => __DIR__ . '/../cache/twig',
			'debug'       => true,
			'auto_reload' => true,
			],
		],
		'logger' => [
			'name' => getenv( 'SITE_NAME' ),
			'path' => __DIR__ . '/../logs/' . $log_name . '.log',
		],
		'database' => [
				'host'   => getenv( 'DB_HOST' ),
				'user'   => getenv( 'DB_USER' ),
				'pass'   => getenv( 'DB_PASS' ),
				'dbname' => getenv( 'DB_NAME' ),
				'port'   => getenv( 'DB_PORT' ),
		],
		'public_routes' => [

		],
	],
];
