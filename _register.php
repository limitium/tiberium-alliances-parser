<?php
$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
require_once $dir . "CnCApi.php";
$localServers = require $dir . "servers.php";
$api = new CnCApi("limitium");
$k = $argv[1];
//foreach ($localServers as $k => $s) {
    if (is_numeric($k)) {
        if ($localServers[$k]['AcceptNewPlayer']) {
            if ($localServers[$k]['u'] != 'limitium@gmail.com') {
                $api->selectWorld($k);
                if ($api->authorize()) {
                    $api->register();
                }
            } else {
                print_r("Registered already\r\n");
            }
        } else {
            print_r("No space\r\n");
        }
    }
//}
$api->saveServers();