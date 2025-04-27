<?php
// Strict requirement: declaration of definitive types
declare(strict_types=1);

// Show all errors with next two lines of code:
error_reporting(E_ALL);  // Report all errors
ini_set('display_errors', true);  // Show errors on webpage, change to false for web-facing servers as a security measure.

//////////////////////////////////////////////////////////////////////
require_once './src/Model.php';
require_once './src/View.php';
require_once './src/Controller.php';

session_start();

$model      = isset($_SESSION['model'])      ? unserialize($_SESSION['model'])      : new Model(logging: true);
$view       = isset($_SESSION['view'])       ? unserialize($_SESSION['view'])       : new View($model, logging: true);
$controller = isset($_SESSION['controller']) ? unserialize($_SESSION['controller']) : new Controller($model, $view, logging: true);

$_SESSION['model']      = serialize($model);
$_SESSION['view']       = serialize($view);
$_SESSION['controller'] = serialize($controller);

$controller->route($_GET, $_POST);
