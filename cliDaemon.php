<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 24/05/2018
 * Time: 3:55 PM
 */


include_once __DIR__ . "/FakeDNSQuery.php";

$command="/sbin/ifconfig | grep 'inet ' | awk '{ print $2}'";
$output = [];
exec ($command, $output);
$defaultHostIp = "0.0.0.0";
foreach ($output as $perIp) {
    echo $perIp."\n\n";
    if (strpos($perIp, "192.") !== false) {
        $defaultHostIp = $perIp;
        break;
    }
}

$hostIp = readline("Current server host ip (default $defaultHostIp): ");
if (!$hostIp) {
    $hostIp = $defaultHostIp;
}
FakeDNSQuery::serve($hostIp);



