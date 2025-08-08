<?php

/**
 * Bootstrap file for PHPUnit tests
 */

// Require Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set up any test-specific configurations
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone for consistent testing
date_default_timezone_set('UTC');

// Define test constants if needed
define('TEST_API_KEY', 'test-api-key');
define('TEST_IPN_SECRET', 'test-secret'); 