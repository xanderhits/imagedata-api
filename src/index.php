<?php
/**
 * @name imagedata-api
 * @author X N Horjus
 * @desc Simple RESTful API that acts as an intermediate
 * between an unreliable source CSV file and a (reliable) JSON translation of it.
 */
require 'vendor/autoload.php';
require 'conf/config.php';

/* Set this to development, test, acceptance or production. */
define('MODE', 'development');

/* Initiate Slim */
$app = new Slim\Slim(array(
    'mode' => MODE,
    'settings' => $settings
));

/* Mode-specific configrations */
require 'conf/configure_modes.php';

/* Various controllers */
require 'controller/error.php';
require 'controller/images.php';
require 'controller/data.php';

$app->response->headers->set('Content-Type', 'application/json');
$app->run();
