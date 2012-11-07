<?php


require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "CnCApi.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "GameObjects.php";

$start = microtime(true);


$serverName = $argv[1];
$api = new CnCApi($serverName);

$api->authorize();

$title = "World minimap!!!";
$msg = "World minimap!
Everyone welcome!
[url]http://map.tiberium-alliances.com/[/url]


";

$post = "
 Added Мир 1, Världen 1, Svet 1, Lume 1, Verden 1, Világ 1, Maailma 1, Verden 1, Svět 1.";
$data = $api->poll(array(
    "requests" => "WC:A\fCTIME:$time\fCHAT:\fWORLD:\fGIFT:\fACS:0\fASS:0\fCAT:0\f"
));
foreach ($data as $part) {
    if ($part->t == "FORUM") {
        $forumId = $part->d->f[1]->i;
        break;
    }
}
if (!$forumId) {
    file_put_contents("c:/WebServers/home/cc/www/parser/spam/errorForum".$serverName, json_encode($data));
    print_r("No forumId found");
}
$fileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . "spam" . DIRECTORY_SEPARATOR . $serverName;
if (!is_file($fileName) || !($threadId = file_get_contents($fileName))) {
    $id = $api->createThread($title, $msg.$post, $forumId);
    file_put_contents($fileName, $id);
} else {
    $api->addPost($post, $forumId, $threadId);
    file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . "spam" . DIRECTORY_SEPARATOR . "p" . $serverName, 1);
}
$api->close();

print_r("Time: " . (microtime(true) - $start));
