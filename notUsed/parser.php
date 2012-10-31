<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "GameObjects.php";

$start = microtime(true);


$serverName = $argv[1];
$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . $serverName;
$world = new World($serverName);
for ($i = 0; $i < 32; $i++) {
    $row = file_get_contents($dir . DIRECTORY_SEPARATOR . $i);
    $data = json_decode($row);
    foreach ($data as $part) {
        if (isset($part->d->__type) && $part->d->__type == "WORLD") {

//            unset($part->d->u);
//            unset($part->d->t);
//            unset($part->d->v);

            $squaresSize = sizeof($part->d->s);
//            print_r(" squares: " . $squaresSize . "\r\n");
//            if ($squaresSize != $server["x"]) {
//                print_r($part);
//                die;
//            }
//            $successParts++;
            foreach ($part->d->s as $squareData) {
                $world->addSquare(Square::decode($squareData));
            }
        }
    }
}
$world->toFile();
print_r("Time: " . (microtime(true) - $start));