<?php
/* General settings */
$config = array(
    'data_dir' => '/hopexs/hits/elements/src/data/',
    'datasource' => 'https://docs.google.com/spreadsheet/ccc?key=0Aqg9JQbnOwBwdEZFN2JKeldGZGFzUWVrNDBsczZxLUE&single=true&gid=0&output=csv',
    'images' => array(
        'valid_image_headers' => array('image/jpeg', 'jpeg', 'png'),
        'max_filesize' => (1024 * 1024) /* in bytes */,
        'imgdir_path' => '/hopexs/hits/elements/src/data/img/',
        'imgdir_url' => 'http://projects.horjus-it.nl/elements/src/data/img/'
    )
);