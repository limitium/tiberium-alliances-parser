<?php
$servers = array_values(require __DIR__ . DIRECTORY_SEPARATOR . "servers.php");

$WshShell = new COM("WScript.Shell");

foreach ($servers as $k => $server) {
    $WshShell->Run("php sendMsg.php " . $servers[$k]['Id'], 7, false);
}