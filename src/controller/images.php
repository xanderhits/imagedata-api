<?php

/* images/index */

$app->get('/images', function() use ($app) {
    $csv_parser = \KzykHys\CsvParser\CsvParser::fromFile('data/data.csv');
    $result = $csv_parser->parse();

    echo json_encode($result);
});


