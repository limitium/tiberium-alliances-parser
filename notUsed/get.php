<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CnCApi.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Grabber.php";

$api = new CnCApi($argv[1]);

if (isset($argv[2])) {
    $api->setSession($argv[2]);
} else {
    $api->authorize();
}

if ($api->isValidSession()) {
    $grabber = new Grabber($api);
    $grabber->parse();
    //    $grabber->load();
    $grabber->writeData();
} else {
    print_r("Wrong api session");
    exit(0);
}





