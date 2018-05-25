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
    /** @var string */
    private $dominio;

    /**
     * FakeDNSQuery constructor.
     * @param string $data
     * @param array $fakeDnsIpMap
     */
    public function __construct($data, $fakeDnsIpMap)
    {
        $this->data = $data;
        $this->fakeDnsIpMap = $fakeDnsIpMap;
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
        echo "get domain $this->dominio" .PHP_EOL;
    }

    public function respuesta()
    {
        $domainName = substr($this->dominio, 0, -1);
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
     * @param null|null $hostIp 本dns服务器的公网ip地址
     */
    public static function serve($hostIp = null)
    {
        $udpSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$hostIp) {
            $hostIp = '192.168.1.122';
        }

        socket_bind($udpSocket, $hostIp, 53);

        $lastReloadTime = time();
        $fakeDnsIpMap = [];
        while (1) {
            //$fakeDnsIpMap 用于动态欺骗自定义域名与ip的绑定数组
            if ((!$fakeDnsIpMap) || ((time() - $lastReloadTime) > 10 )  ) {
                $fakeDnsIpMap = self::reloadFakeDnsIpMap();
                var_dump($fakeDnsIpMap);
                $lastReloadTime = time();
            }
//            $fakeDnsIpMap = [
//                //..
//                "www.fakewang22.com" => '14.215.177.38',
//                //..
//            ];
            if (!$fakeDnsIpMap) {
                continue;
            }
            echo sprintf("Server ip: %s for fakeDnsIpMap %s \n", $hostIp, json_encode($fakeDnsIpMap));

            $fromIp = '';
            $fromPort = null;
            //这里会阻塞
            socket_recvfrom($udpSocket, $dnsQueryData, 1024, 0, $fromIp, $fromPort);
            echo "received udp data\n";
            //echo "from remote address $fromIp and remote port $fromPort" . PHP_EOL;
            $dq = new self($dnsQueryData, $fakeDnsIpMap);
            $respuestaData = $dq->respuesta();
            if ($respuestaData) {
                socket_sendto($udpSocket, $respuestaData, strlen($respuestaData), 0, $fromIp, $fromPort);
            }
        }
    }

    /**
     * @return array
     */
    private static function reloadFakeDnsIpMap()
    {
        $isMetaDataExist = file_exists(__DIR__ . "/data.txt");
        if (!$isMetaDataExist) {
            echo "No data.txt \n";
            return [];
        } else {
            $dataStr = file_get_contents(__DIR__ . "/data.txt");
            if (!$dataStr) {
                return [];
            }
            $fakeDnsIpMap = unserialize($dataStr);
        }

        if (empty($fakeDnsIpMap)) {
            return [];
        }
        return $fakeDnsIpMap;
    }


}
