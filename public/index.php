<?php

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Load .env file with secret stuff.
$dotenv = Dotenv\Dotenv::create( __DIR__ . '/../' );
$dotenv->load();

// Instantiate the app.
$settings = require __DIR__ . '/../app/settings.php';
$app      = new \Slim\App( $settings );

// Set up dependencies.
require __DIR__ . '/../app/dependencies.php';

// Register middleware
require __DIR__ . '/../app/middleware.php';

// Register routes.
require __DIR__ . '/../app/routes.php';

// Run.
$app->run();
