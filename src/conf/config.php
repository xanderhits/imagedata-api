<?php
/* General settings */
$settings = array(
    'data_dir' => '/path/to/data/',
    'datasource' => 'https://docs.google.com/spreadsheet/ccc?key=0Aqg9JQbnOwBwdEZFN2JKeldGZGFzUWVrNDBsczZxLUE&single=true&gid=0&output=csv',
    'images' => array(
        'max_desc_length' => 50, /* maximum image description length in amount of chars */
        'valid_image_headers' => array('image/jpeg', 'image/png'),
        'max_filesize' => (500 * 1024) /* in bytes */,
        'imgdir_path' => 'path/to/data/img/',
        'imgdir_url' => 'http://url.to/data/img/'
    )
);