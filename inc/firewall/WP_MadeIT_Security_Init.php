<?php

class WP_MadeIT_Security_Init
{
    private $blockIp = [

    ];

    public function getIp()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = 'UNKNOWN';
        }

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
    
    public function loadBlockedIps()
    {
        if(file_exists(MADEIT_SECURITY_LOG_PATH . '/wp-security-blocks.php')) {
            try {
                require_once MADEIT_SECURITY_LOG_PATH . '/wp-security-blocks.php';
                if(isset($wp_security_by_madeit_ip_blocks) && is_array($wp_security_by_madeit_ip_blocks)) {
                    $this->blockIp = $wp_security_by_madeit_ip_blocks;
                }
            }
            catch(Exception $e) {
                error_log($e->getMessage());
            }
        }
    }
}

define('MADEIT_SECURITY_FIREWALL_ENABLED', true);

$madeit_security_firewall_init = new WP_MadeIT_Security_Init();
$ipaddress = $madeit_security_firewall_init->getIp();
$madeit_security_firewall_init->loadBlockedIps();

$isIpv4 = $madeit_security_firewall_init->isIpv4($ipaddress);
$requestedUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http')."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : null;
$https = isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) == 'ON' ? true : false;
$serverIpaddress = $_SERVER['SERVER_ADDR'];

$requestData = [
    'ip'           => $ipaddress,
    'isIpv4'       => $isIpv4,
    'requestedUrl' => $requestedUrl,
    'referer'      => $referer,
    'userAgent'    => $userAgent,
    'protocol'     => $protocol,
    'https'        => $https,
    'blocked'      => false,
    'block_reason' => null,
];

if ($madeit_security_firewall_init->isBlockedIpBlocked($ipaddress)) {
    $requestData['blocked'] = true;
    $requestData['block_reason'] = 'IP Blocked';
}

if ($requestData['blocked']) {
    header('HTTP/1.1 403 Forbidden');
    die('You are not allowed to visit this page.');
}

if ($ipaddress == $serverIpaddress) {
    if (isset($_GET['firewall_action']) && $_GET['firewall_action'] == 'install_status') {
        die(json_encode(['success' => true]));
    }
}
