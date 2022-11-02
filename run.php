<?php

use Russell\Chipmaker\App;

require __DIR__ . '/vendor/autoload.php';
/*
Take the CSV provided by the teams, 
and generate a CHIP-0007 compatible json, 
calculate the sha256 of the json file and append it to each line in the csv (as a filename.output.csv)
*/


// execute it
$cli = new App();
$cli->run();
