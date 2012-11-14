<?php
error_reporting(E_ALL);
date_default_timezone_set("UTC");

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CnCApi.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "GameObjects.php";

$start = microtime(true);

$serverId = $argv[1];
$api = new CnCApi($serverId, isset($argv[2]) ? $argv[2] : false);

if ($api->authorize()) {
    print_r("Time: " . (microtime(true) - $start) . "\r\n");
    $getStart = microtime(true);
    $world = new World($serverId);

    $time = CnCApi::getTime();

    $resp = $api->poll(array(
        "requests" => "WC:A\fCTIME:$time\fCHAT:\fWORLD:\fGIFT:\fACS:0\fASS:0\fCAT:0\f"
    ));

    $server = $api->servers[$serverId];
    $successParts = 0;
    $squares = array();
    for ($y = 0; $y <= $server["y"]; $y += 1) {

        $request = $world->request(0, $y, $server["x"], $y);

        $time = CnCApi::getTime();
        $resp = $api->poll(array(
            "requests" => "UA\fWC:A\fCTIME:$time\fCHAT:\fWORLD:$request\fGIFT:\fACS:1\fASS:1\fCAT:1\f"
        ), true);


        if ($resp) {
            $data = json_decode($resp);
            if ($data) {
                $squares = array();
                print_r("Row: $y");
                foreach ($data as $part) {
                    if (isset($part->d->__type) && $part->d->__type == "WORLD") {

                        unset($part->d->u);
                        unset($part->d->t);
                        unset($part->d->v);

                        $squaresSize = sizeof($part->d->s);
                        print_r(" squares: " . $squaresSize . "\r\n");
                        if ($squaresSize != $server["x"]) {
                            $api->close();
                            die;
                        } else {
                            $successParts++;
                        }
                        foreach ($part->d->s as $squareData) {
                            $world->addSquare(Square::decode($squareData));
                        }
                    }
                }
            }
        }
    }
    print_r("\r\nSucces parts:$successParts, time: " . (microtime(true) - $getStart) . " \r\n");
    $decodeStart = microtime(1);
    foreach ($squares as $squareData) {
        $world->addSquare(Square::decode($squareData));
    }
    print_r("\r\nDecoded, time: " . (microtime(true) - $decodeStart) . " \r\n");
    if ($successParts == $server["y"]) {
        $uploadStart = microtime(true);
        print_r("Uploading: " . $world->toServer() . ", time: " . (microtime(true) - $uploadStart) . "\r\n");
    }
    print_r("Total time: " . (microtime(true) - $start));
}
$api->close();