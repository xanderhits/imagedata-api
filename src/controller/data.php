<?php

/**
 * Route /data/update
 * - Download the CSV from source
 * - Parse it, filtering faulty lines
 * - Save it to JSON on disk for fast serving
 */
$app->get('/data/update', function() use ($app) {
    $settings = $app->config('settings');

    /* Can we even download this CSV? */
    if($csv = file_get_contents($settings['datasource'])) {
        /* Apparently. Let's parse it. */
        $content = \KzykHys\CsvParser\CsvParser::fromString($csv)->parse();

        try {
            /* Save our new data file to local disk. */
            $open = fopen($settings['data_dir'] . 'data.json', 'w+');
            fputs($open, json_encode($content));
            fclose($open);

            $app->log->notice('Updated data.json');

            $body = array(
                'success' => true,
                'message' => null,
                'data' => $content
            );

            echo json_encode($body);
        }
        catch(ErrorException $e) {
            $app->log->error('Could not write to data.json: ' . $e->getMessage());

            throw new ErrorException($e->getMessage());
        }
    }
    else {
        $app->log->error('Could not download the CSV source file!');

        throw new ErrorException('Could not retrieve the source file located at ' .
            $settings['datasource']);
    }
});

/**
 * Method to parse faulty lines from the CSV file.
 * Faulty lines are defined as:
 * - Being incomplete (i.e. there are less than 3 fields of data)
 * - Having an incorrect image address (not a legit URL), image is inaccessible or is not an
 *   image file
 * - Having more than 3 fields.
 * - Name or URL field is empty.
 * In these cases, the entry is skipped.
 *
 * Some lines can easily be fixed:
 * - Truncate leading and trailing apostrophes
 * - Break off descriptions that are too long with ellipsis
 * - Convert newlines to <br> tags?
 */
function parseCsv($data, $app) {
    $valid = array();
    $invalid = array();

    for($i = 0; $i < count($data); $i++) {
        $d = $data[$i];

        /* Does not contain exactly 3 fields; invalid */
        if(count($d) != 3) {
            $invalid[$i] = 'Is not a data triplet!';
            continue;
        }

        /* Name or URL fields are empty */
        if(empty($d[0]) || empty($d[2])) {
            $invalid[$i] = 'Name or URL field was empty!';
            continue;
        }

        /* Image URL was invalid. */
        $img = storeImage($d[2], $app);
        if(!$img) {
            $invalid[$i] = 'Image could not be downloaded or was not valid!';
            continue;
        }

        /* Everything's fine. Add! */
        $valid[] = array(
            'title' => $d[0],
            'description' => $d[1],
            'image' => $img
        );
    }

    return array(
        'valid' => $valid,
        'invalid' => $invalid
    );
}

/**
 * Method to handle an image address. This is done as follows:
 * - Try to retrieve the file (upon failure: return false)
 * - Reduce it to a maximum height or width as defined in the configuration
 * - Save it to disk, return the url
 */
function storeImage($url, $app) {
    $settings = $app->config('settings');
    $local_path = $settings['images']['imgdir_path'];
    $filename = basename($url);

    try {
        $copied = copy($url, $local_path . $filename);
    }
    catch(ErrorException $e) {
        $app->log->warn('The following image could not be downloaded for local storage: ' . $url);

        return false;
    }

    $app->log->debug('Downloaded and stored image with URL ' . $url);

    /* We have the image downloaded, now lets check its header if it's even an image */
    return $settings['images']['imgdir_url'] . $filename;
}

$app->get('/data/test', function() use ($app) {
    $settings = $app->config('settings');

    $csv = file_get_contents($settings['datasource']);
    $data = \KzykHys\CsvParser\CsvParser::fromString($csv)->parse();

    var_dump(parseCsv($data, $app));
});