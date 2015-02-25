<?php
/* Route for /images/index
 * Retrieve the JSON data file and pass it to the user. */
$app->get('/images', function() use ($app) {
    $settings = $app->config('settings');

    /* Try retrieving the file and displaying it. */
    try {
        $data = file_get_contents($settings['data_dir'] . 'data.json');

        $output = array(
            'status' => 'success',
            'message' => null,
            'data' => json_decode($data)
        );

        echo json_encode($output);
    }
    catch(ErrorException $e) {
        $app->log->warning("Could not open the data file: " . $e->getMessage());

        throw new ErrorException($e->getMessage());
    }
});