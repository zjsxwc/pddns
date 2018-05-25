<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 24/05/2018
 * Time: 3:55 PM
 */


include_once __DIR__ . "/FakeDNSQuery.php";

$defaultHostIp = "192.168.1.122";
$hostIp = readline("Current server host ip (default $defaultHostIp): ");
if (!$hostIp) {
    $hostIp = $defaultHostIp;
}
FakeDNSQuery::serve($hostIp);



