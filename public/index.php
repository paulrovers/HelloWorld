<?php
use Symfony\Component\Dotenv\Dotenv;
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 100);
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 100);
session_set_cookie_params(60 * 60 * 24 * 100);
session_start();

ini_set('date.timezone', 'Europe/Berlin');

/**
 * Composer
 */
require '../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load('../.env');

/**
 * Error and Exception handling
 */
error_reporting(E_ALL & ~E_NOTICE);
set_error_handler('core\Error::errorHandler');
set_exception_handler('core\Error::exceptionHandler');

$router = new core\Router();
require('../app/Routes.php');
$router->dispatch($_SERVER['QUERY_STRING']);
?>
