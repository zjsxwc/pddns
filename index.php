<?php



$fakeDomain = $_GET["fakeDomain"];//"my-domain.test"
if (!$fakeDomain) {
    echo "Need query param [fakeDomain]";
    exit;
}
$ip = $_GET["ip"];
if (!$ip) {
    echo "Need query param [ip]";
    exit;
}


$fakeDnsIpMap = [
    $fakeDomain => $ip
];

//var_dump($fakeDnsIpMap);


$data = serialize($fakeDnsIpMap);
if (file_put_contents('data.txt', $data)) {
    echo "OK";
} else {
    echo "Fail";
}
