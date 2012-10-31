<?php

$parts = 3;
$delay = 120;

$servers = array_values(require __DIR__ . DIRECTORY_SEPARATOR . "servers.php");
$perCol = ceil(sizeof($servers) / $parts);

$WshShell = new COM("WScript.Shell");
while (true) {
    for ($i = 0; $i <= $parts - 1; $i++) {
        print_r("Part " . ($i + 1) . "\r\n");
        for ($k = $i * $perCol; $k < ($i + 1) * $perCol; $k++) {
            if ($k == sizeof($servers)) {
                break;
            }
            if (isset($servers[$k]['Id'])) {
                $WshShell->Run("php getWorld.php " . $servers[$k]['Id'], 7, false);
            }
        }
        sleep($delay);
    }
    print_r("\r\n");
}
