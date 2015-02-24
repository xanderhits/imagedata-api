<?php

/* data/update */

$app->get('/data/update', function() use ($app) {
    $settings = $app->config('settings');

    /* Can we even download this CSV? */
    if($csv = file_get_contents($settings['datasource'])) {
        /* Apparently. Let's parse it. */
        $csv_parser = \KzykHys\CsvParser\CsvParser::fromString($csv);
        $content = $csv_parser->parse();

        try {
            /* Save our new data file to local disk. */
            $open = fopen($settings['data_dir'] . 'data.json', 'w+');
            fputs($open, json_encode($content));
            fclose($open);

            $body = array(
                'success' => true,
                'message' => null,
                'data' => $content
            );
        }
        catch(Exception $e) {
            $body = array(
                'success' => false,
                'message' => 'The data file could not be written to disk!',
            );
        }
    }
    else {
        $body = array(
            'success' => false,
            'message' => 'The source file could not be retrieved!'
        );
    }

    echo json_encode($body);
});