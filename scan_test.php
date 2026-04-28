<?php

require 'vendor/autoload.php';

$generator = new \OpenApi\Generator();
$openapi = $generator->generate(['app'], validate: false);
if ($openapi) {
    echo $openapi->toJson(JSON_PRETTY_PRINT) . PHP_EOL;
} else {
    echo "NULL result\n";
}
