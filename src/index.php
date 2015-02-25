<?php
/**
 * @name imagedata-api
 * @author X N Horjus
 * @desc Simple RESTful API that acts as an intermediate
 * between an unreliable source CSV file and a (reliable) JSON translation of it.
 */
require 'vendor/autoload.php';
require 'conf/config.global.php';

/* Set this to development, test, acceptance or production. */
define('MODE', 'development');

/* Include mode-specific configuration */
$mode_config_file = 'config/config' . MODE . '.php';
if(file_exists($mode_config_file))
    include($mode_config_file);

/* Initiate Slim */
$app = new Slim\Slim(array(
    'mode' => MODE,
    'settings' => $config
));

include('conf/configure_modes.php');

/* Various controllers */
include('controller/error.php');
include('controller/images.php');
include('controller/data.php');

$app->response->headers->set('Content-Type', 'application/json');
$app->run();
