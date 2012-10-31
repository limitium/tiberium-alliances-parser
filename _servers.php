<?php
$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
require_once $dir . "CnCApi.php";
$api = new CnCApi("util");
$api->checkNewServers();
$api->saveServers();
