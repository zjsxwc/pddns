<?php

/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 23/05/2018
 * Time: 3:52 PM
 */
class FakeDNSQuery
{
    /** @var string */
    private $data;
    /** @var array */
    private $fakeDnsIpMap;
    private $fakeDnsKeywords;
    /** @var string */
    private $dominio;

    /**
     * FakeDNSQuery constructor.
     * @param string $data
     * @param array $fakeDnsIpMap
     * @param string[] $fakeDnsKeywords
     */
    public function __construct($data, $fakeDnsIpMap, $fakeDnsKeywords = [])
    {
        $this->data = $data;
        $this->fakeDnsIpMap = $fakeDnsIpMap;
        $this->fakeDnsKeywords = $fakeDnsKeywords;
        $this->dominio = "";
        $tipo = (ord($data{2}) >> 3) & 15;
        if ($tipo == 0) {
            $ini = 12;
            $lon = ord($data{$ini});
            while ($lon != 0) {
                $subDominio = substr($data, $ini + 1, $lon);
                $this->dominio .= $subDominio . '.';
                $ini += ($lon + 1);
                $lon = ord($data{$ini});
            }
        }
        echo "get domain $this->dominio" . PHP_EOL;
    }

    public function respuesta()
    {
        $domainName = substr($this->dominio, 0, -1);

        foreach ($this->fakeDnsKeywords as $fakeDnsKeyword) {
            if (strpos($domainName, $fakeDnsKeyword) !== false) {
                $this->fakeDnsIpMap[$domainName] = "0.0.0.0";
            }
        }

        $isExistFakeDomainInMap = isset($this->fakeDnsIpMap[$domainName]);

        echo "try to fake $this->dominio ($domainName) \n";
        $packet = '';
        if ($isExistFakeDomainInMap) {
            $fakeDnsIp = $this->fakeDnsIpMap[$domainName];
            echo "find fake $domainName map to $fakeDnsIp \n";
        } else {
            echo "no fake $domainName \n";
            var_dump($this->fakeDnsIpMap);
            return $packet;
        }
        if ($this->dominio && isset($this->fakeDnsIpMap[$domainName])) {
            $fakeDnsIp = $this->fakeDnsIpMap[$domainName];
            echo ">>>>>>>>>>>>>>>       start to fake domain $this->dominio to $fakeDnsIp " . PHP_EOL;
            $subStr = substr($this->data, 0, 2);
            $packet .= $subStr . "\x81\x80";
            $subStr = substr($this->data, 4, 2);
            $packet .= $subStr . $subStr . "\x00\x00\x00\x00";
            $subStr = substr($this->data, 12);
            $packet .= $subStr;
            $packet .= "\xc0\x0c";
            $packet .= "\x00\x01\x00\x01\x00\x00\x00\x3c\x00\x04";
            $ipSubStr = "";
            foreach (explode(".", $fakeDnsIp) as $perIpSeg) {
                $ipSubStr .= chr(intval($perIpSeg));
            }
            $packet .= $ipSubStr;
        }
        return $packet;
    }


    /**
     * @param null|string $hostIp 本dns服务器的公网ip地址
     */
    public static function serve($hostIp = null)
    {
        $udpSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$hostIp) {
            $hostIp = '192.168.1.122';
        }

        socket_bind($udpSocket, $hostIp, 53);

        $fakeDnsKeywords = ["weibo", "sina"];
        $fakeDnsIpMap = [];
//            $fakeDnsIpMap 用于动态欺骗自定义域名与ip的绑定数组
//            $fakeDnsIpMap = [
//                //..
//                "www.fakewang22.com" => '14.215.177.38',
//                //..
//            ];
        while (1) {
            if ((!$fakeDnsIpMap) && (!$fakeDnsKeywords)) {
                continue;
            }
            echo sprintf("Server ip: %s for fakeDnsIpMap %s \n", $hostIp, json_encode($fakeDnsIpMap));

            $fromIp = '';
            $fromPort = null;
            //这里会阻塞
            socket_recvfrom($udpSocket, $dnsQueryData, 1024, 0, $fromIp, $fromPort);
            echo "received udp data\n";
            //echo "from remote address $fromIp and remote port $fromPort" . PHP_EOL;
            $dq = new self($dnsQueryData, $fakeDnsIpMap, $fakeDnsKeywords);
            $respuestaData = $dq->respuesta();
            if ($respuestaData) {
                socket_sendto($udpSocket, $respuestaData, strlen($respuestaData), 0, $fromIp, $fromPort);
            }
        }
    }


}
