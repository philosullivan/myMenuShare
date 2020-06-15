<?php
/**
 * Routes
 *
 * @package N/A.
 * @author Phil O'Sullivan <phil.osullivan@gmail.com>.
 */

 // Load home page.
$app->get( '/', App\Action\IndexAction::class . ':load_index' )->setName( 'load_index' );

 // Load sign up page.
 $app->get( '/sign-up', App\Action\IndexAction::class . ':load_sign_up' )->setName( 'load_sign_up' );
 $app->post( '/sign-up', App\Action\IndexAction::class . ':post_sign_up' )->setName( 'post_sign_up' );

// Load log in page.
$app->get( '/log-in', App\Action\IndexAction::class . ':load_log_in' )->setName( 'load_log_in' );

// Load about us page.
$app->get( '/about-us', App\Action\IndexAction::class . ':load_about_us' )->setName( 'load_about_us' );

// Load contact us page.
$app->get( '/contact-us', App\Action\IndexAction::class . ':load_contact_us' )->setName( 'load_contact_us' );

// Load contact us page.
$app->get( '/help', App\Action\IndexAction::class . ':load_help' )->setName( 'load_help' );

// Load test page.
$app->get( '/test', App\Action\IndexAction::class . ':load_test' )->setName( 'load_test' );

/**
 * Verify user.
 *
 * @param string {verification_token} generated and emailed verification token.
 */
$app->get( '/verify', App\Action\UsersAction::class . ':user_verify' )->setName( 'get.verify.user' );
