<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 24/05/2018
 * Time: 3:55 PM
 */


include_once __DIR__ . "/FakeDNSQuery.php";


$hostIp = readline("Current server host ip (default 127.0.0.1): ");
if (!$hostIp) {
    $hostIp = "127.0.0.1";
}
FakeDNSQuery::serve($hostIp);



