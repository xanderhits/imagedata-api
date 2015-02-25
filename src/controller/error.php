<?php
/**
 * Routes for errors
 */

/**
 * Generic error handler
 */
$app->error(function (\Exception $e) use ($app) {
    echo json_encode(array(
        'success' => false,
        'message' => 'An error occured which prevented us from serving this page.',
    ));
});

/**
 * 404 not found handler
 */
$app->notFound(function () use ($app) {
    echo json_encode(array(
        'success' => false,
        'message' => '404 error: action not found.',
    ));
});