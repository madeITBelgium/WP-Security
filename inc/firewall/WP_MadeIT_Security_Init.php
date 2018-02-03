<?php

class WP_MadeIT_Security_Init {
    private $blockIp = [
        
    ];
    
    public function getIp()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ip = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = 'UNKNOWN';
        return $ip;
    }
    
    public function isIpv4($ip)
    {
        return strpos($ip, ':') === -1;
    }
    
    public function isBlockedIpBlocked($ip)
    {
        return in_array($ip, $this->blockIp);
    }
}

define('MADEIT_SECURITY_FIREWALL_ENABLED', true);

$madeit_security_firewall_init = new WP_MadeIT_Security_Init();
$ipaddress = $madeit_security_firewall_init->getIp();
$isIpv4 = $madeit_security_firewall_init->isIpv4($ipaddress);
$requestedUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null;
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : null;
$https = isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) == 'ON' ? true : false;
$serverIpaddress = $_SERVER['SERVER_ADDR'];

$requestData = array(
    'ip' => $ipaddress,
    'isIpv4' => $isIpv4,
    'requestedUrl' => $requestedUrl,
    'referer' => $referer,
    'userAgent' => $userAgent,
    'protocol' => $protocol,
    'https' => $https,
    'blocked' => false,
    'block_reason' => null,
);

if($madeit_security_firewall_init->isBlockedIpBlocked($ipaddress))
{
    $requestData['blocked'] = true;
    $requestData['block_reason'] = 'IP Blocked';
}

if($requestData['blocked'])
{
    header("HTTP/1.1 403 Forbidden");
    die('You are not allowed to visit this page.');
}

if($ipaddress == $serverIpaddress)
{
    if(isset($_GET['firewall_action']) && $_GET['firewall_action'] == 'install_status') {
        die(json_encode(array('success' => true)));
    }
}