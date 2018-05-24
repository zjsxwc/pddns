<?php


function getIp()
{

    if(!empty($_SERVER["HTTP_CLIENT_IP"]))
    {
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    }
    else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    else if(!empty($_SERVER["REMOTE_ADDR"]))
    {
        $cip = $_SERVER["REMOTE_ADDR"];
    }
    else
    {
        $cip = '';
    }
    preg_match("/[\d\.]{7,15}/", $cip, $cips);
    $cip = isset($cips[0]) ? $cips[0] : 'unknown';
    unset($cips);

    return $cip;
}


$fakeDomain = $_GET["fakeDomain"];//"my-domain.test"
$ip = $_GET["ip"];
if (!$ip) {
    $ip = getIp();
}


$fakeDnsIpMap = [
    $fakeDomain => $ip
];

//var_dump($fakeDnsIpMap);

$varStr = var_export($fakeDnsIpMap, true);
$var = "<?php\n\$fakeDnsIpMap = $varStr;\n\n?>";
file_put_contents('metaData.php', $var);

echo "OK";
