<?php


require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CnCApi.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "GameObjects.php";

$start = microtime(true);


$serverName = $argv[1];
$api = new CnCApi($serverName);

$api->authorize();


$world = new World($serverName);

$time = time();

$resp = $api->poll(array(
    "requests" => "WC:A\fCTIME:$time\fCHAT:\fWORLD:\fGIFT:\fACS:0\fASS:0\fCAT:0\f"
));
$api->close();
$server = $api->servers[$serverName];

$pollRequests = 1;
$cmh = curl_multi_init();
$tasks_curl = array();
for ($y = 0; $y <= $server["y"]; $y += 1) {

    $req = $world->request(0, $y, $server["x"], $y);

    $time = time();

    $data = array(
        "requests" => "UA\fWC:A\fCTIME:$time\fCHAT:\fWORLD:$req\fGIFT:\fACS:1\fASS:1\fCAT:1\f",
        'requestid' => $pollRequests,
        'sequenceid' => $pollRequests,
        'session' => $api->getSession()
    );

    $host = $api->getHost();
    $url = "http://$host/" . $api->getUrl();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . "/Presentation/Service.svc/ajaxEndpoint/Poll");
    curl_setopt($ch, CURLOPT_TIMEOUT, 55);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $host",
        "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:10.0.2) Gecko/20100101 Firefox/10.0.2",
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Content-Type: application/json; charset=UTF-8",
        "X-Qooxdoo-Response-Type: application/json",
        "Referer: $url/index.aspx",
        "Pragma: no-cache",
        "Cache-Control: no-cache"
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $tasks_curl[$y] = $ch;
    curl_multi_add_handle($cmh, $ch);
    $pollRequests++;
}
$active = null;
do {
    $mrc = curl_multi_exec($cmh, $active);
}
while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && ($mrc == CURLM_OK)) {
    do {
        $mrc = curl_multi_exec($cmh, $active);
        $info = curl_multi_info_read($cmh);
        if ($info['msg'] == CURLMSG_DONE) {
            $ch = $info['handle'];
            $y = array_search($ch, $tasks_curl);
            $tasks_curl[$y] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($cmh, $ch);
            curl_close($ch);
        }
    }
    while ($mrc == CURLM_CALL_MULTI_PERFORM);
}
curl_multi_close($cmh);

//$successParts = 0;
foreach ($tasks_curl as $y => $resp) {
    if (!is_string($resp)) {
        continue;
    }
    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . $serverName;
    if (!is_dir($dir)) {
        mkdir($dir);
    }
    $fileName = $dir . DIRECTORY_SEPARATOR . $y;
    file_put_contents($fileName, $resp);

//    file_put_contents("c:/1/{$serverName}_$y.json", $resp);
//    $successParts++;
//    continue;
//    $data = json_decode($resp);
//    if (!$data) {
//        continue;
//    }
//    $squares = array();
//    print_r("Row: $y");
//    foreach ($data as $part) {
//        if (isset($part->d->__type) && $part->d->__type == "WORLD") {
//
//            unset($part->d->u);
//            unset($part->d->t);
//            unset($part->d->v);
//
//            $squaresSize = sizeof($part->d->s);
//            print_r(" squares: " . $squaresSize . "\r\n");
//            if ($squaresSize != $server["x"]) {
//                print_r($part);
//                die;
//            }
//            $successParts++;
//            foreach ($part->d->s as $squareData) {
//                $world->addSquare(Square::decode($squareData));
//            }
//        }
//    }

}
//print_r("\r\n\r\nSucces parts:$successParts\r\n");
//if ($successParts == $server["y"]) {
//    $world->toFile();
//}
print_r("Time: " . (microtime(true) - $start));
