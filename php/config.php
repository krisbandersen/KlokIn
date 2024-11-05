<?php

	// Database Credentials
	define('DB_HOST', 'localhost');
	define('DB_DATABASE', 'klokindb');
	define('DB_USERNAME', 'root');
	define('DB_PASSWORD', '');

	// Email Credentials
	define('SMTP_HOST', 'smtp-relay.brevo.com');
	define('SMTP_PORT', 587);
	define('SMTP_USERNAME', '7c6c26001@smtp-brevo.com');
	define('SMTP_PASSWORD', 'xrXR5Bcd6wQ9Ft8g');
	define('SMTP_FROM', 'krisbandersen05@gmail.com');
	define('SMTP_FROM_NAME', 'KlokIn');

	// Global Variables
	define('MAX_LOGIN_ATTEMPTS_PER_HOUR', 10);
	define('MAX_EMAIL_VERIFICATION_REQUESTS_PER_DAY', 3);
	define('MAX_PASSWORD_RESET_REQUESTS_PER_DAY', 3);
	define('PASSWORD_RESET_REQUEST_EXPIRY_TIME', 60*60);
	define('CSRF_TOKEN_SECRET', '<Tristan er bøsse>');
	define('VALIDATE_EMAIL_ENDPOINT', 'http://localhost/php/validate'); 
	define('RESET_PASSWORD_ENDPOINT', 'http://localhost/php/resetpassword');

	// Code we want to run on every page/script
	date_default_timezone_set('UTC'); 
	error_reporting(0);
	session_set_cookie_params(['samesite' => 'Strict']);
	session_start();
	
