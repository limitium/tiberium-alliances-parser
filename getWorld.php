<?php


require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CnCApi.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "GameObjects.php";

$start = microtime(true);

$serverId = $argv[1];
$api = new CnCApi($serverId, isset($argv[2]) ? $argv[2] : false);

if ($api->authorize()) {

    $world = new World($serverId);

    $time = time();

    $resp = $api->poll(array(
        "requests" => "WC:A\fCTIME:$time\fCHAT:\fWORLD:\fGIFT:\fACS:0\fASS:0\fCAT:0\f"
    ));

    $server = $api->servers[$serverId];
    $successParts = 0;
    //    for ($x = 0; $x < 31; $x += 1) {
    //        print_r("$x - " . ($x + 1) . " \r\n");
    for ($y = 0; $y <= $server["y"]; $y += 1) {
        //            print_r("   $y - " . ($y + 1) . " \r\n");
        //            $world = request($x, $x + 1, $y, $y + 1);

        $request = $world->request(0, $y, $server["x"], $y);

        $time = time();
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
    print_r("\r\nSucces parts:$successParts\r\n");
    if ($successParts == $server["y"]) {
        $uploadStart = microtime(true);
        print_r("Uploading: " . $world->toServer() . ", time: " . (microtime(true) - $uploadStart) . "\r\n");
    }
    //    }
    print_r("Total time: " . (microtime(true) - $start));
}

$api->close();