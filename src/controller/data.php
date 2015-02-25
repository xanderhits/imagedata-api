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
        $data = \KzykHys\CsvParser\CsvParser::fromString($csv)->parse();
        $parsed = parseCsv($data, $app);

        try {
            /* Save the valid lines to a data.json file in order to serve to users. */
            $open = fopen($settings['data_dir'] . 'data.json', 'w+');
            fputs($open, json_encode($parsed['valid']));
            fclose($open);

            $app->log->notice('Updated data.json');

            $body = array(
                'success' => true,
                'message' => null,
                'data' => $parsed
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
 * Furthermore, we should break off descriptions that are too long.
 * In addition, we might want to parse htmlspecialchars() or, alternatively, convert nl2br's
 * (depending on if HTML is allowed in the mobile app or not). Since the assignment was not clear
 * on this, neither was implemented.
 */
function parseCsv($data, $app) {
    $settings = $app->config('settings');

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
            $invalid[$i] = 'Image could not be downloaded, was not a valid image type or was too large!';
            continue;
        }

        /* Check if name field is too long, otherwise break it off */
        if(strlen($d[0]) > $settings['images']['max_desc_length']) {
            $d[0] = substr($d[0], 0, $settings['images']['max_desc_length']) . '...';
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
    $local_file = $local_path . $filename;

    /* Attempt the download. */
    try {
        $copied = copy($url, $local_file);
    }
    catch(ErrorException $e) {
        $app->log->warn('The following image could not be downloaded for local storage: ' . $url);

        return false;
    }

    /* Success: let's read it first and check if file type is acceptable. */
    $app->log->debug('Downloaded and stored image with URL ' . $url);

    $mime = get_file_type($local_file);
    $size = filesize($local_file);

    /* If type is not acceptable or size is too large, this is not a valid image. */
    if(
        (!in_array($mime, $settings['images']['valid_image_headers'])) ||
        ($size > $settings['images']['max_filesize'])
    ) {
        $app->log->debug('Downloaded file was not proper type or too large, deleting...');
        unlink($local_file);

        return false;
    }

    $app->log->debug('File passed checks. Adding.');

    return $settings['images']['imgdir_url'] . $filename;
}

/**
 * Retrieve file info
 */
function get_file_type($path) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $path);
    finfo_close($finfo);

    return $mime;
}