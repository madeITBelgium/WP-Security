<?php

class WP_MadeIT_Security_SystemInfo
{
    public function getPHPVersion()
    {
        return phpversion();
    }

    public function getMySQLVersion()
    {
        global $wpdb;
        if (method_exists($wpdb, 'db_version')) {
            return preg_replace('/[^0-9.].*/', '', $wpdb->db_version());
        }

        return 'N/A';
    }

    public function getApacheVersion()
    {
        $result = '';

        try {
            if (function_exists('apache_get_version')) {
                preg_match('/[0-9\.]+/', apache_get_version(), $matches);
                $result = $matches[0];
            }
        } catch (Exception $e) {
        }

        return $result;
    }

    public function getHomeUrl()
    {
        if (is_multisite()) {
            return $this->fixSchema(network_site_url());
        }

        return $this->fixSchema(home_url('/'));
    }

    public function getAdminUrl()
    {
        return $this->fixSchema(get_admin_url());
    }

    public function getUserCount()
    {
        if (is_multisite()) {
            $users = get_user_count();
        } else {
            $users = count_users();
        }

        if (isset($users['total_users'])) {
            return $users['total_users'];
        } else {
            return 0;
        }
    }

    public function getWPPath()
    {
        return ABSPATH;
    }

    public function getSystemInfo($humanReadable = false)
    {
        global $wp_local_package;
        // include an unmodified $wp_version
        include ABSPATH.WPINC.'/version.php';
        $php_version = $this->getPHPVersion();
        $mysql_version = $this->getMySQLVersion();
        $apache_version = $this->getApacheVersion();

        if (is_multisite()) {
            $num_blogs = get_blog_count();
            $multisite_enabled = 1;
        } else {
            $multisite_enabled = 0;
            $num_blogs = 1;
        }

        $user_count = $this->getUserCount();
        $wp_install = $this->getHomeUrl();
        $path = $this->getWPPath();

        $osInfo = $this->getOSInformation();
        $wp_admin_url = $this->getAdminUrl();

        $systeminfo = [
            'php_version'      => $php_version,
            'mysql_version'    => $mysql_version,
            'wp_version'       => $wp_version,
            'apache_version'   => $apache_version,
            'url'              => $wp_install,
            'admin_url'        => $wp_admin_url,
            'user_count'       => $user_count,
            'site_count'       => $num_blogs,
            'path'             => $path,
            'os_name'          => isset($osInfo['name']) ? $osInfo['name'] : null,
            'os_version'       => isset($osInfo['version_id']) ? $osInfo['version_id'] : null,
            'memory_limit'     => $this->getMemoryLimit(),
            'free_disk_space'  => $humanReadable ? $this->bytesToHuman($this->freeDiskSpace()) : $this->freeDiskSpace(),
            'total_disk_space' => $humanReadable ? $this->bytesToHuman($this->totalDiskSpace()) : $this->totalDiskSpace(),
        ];

        return $systeminfo;
    }

    private function getOSInformation()
    {
        if (false == function_exists('shell_exec')) {
            return;
        }

        //Check Ubuntu
        $os = shell_exec('cat /etc/os-release 2>/dev/null');
        $listIds = preg_match_all('/.*=/', $os, $matchListIds);
        $listIds = $matchListIds[0];

        $listVal = preg_match_all('/=.*/', $os, $matchListVal);
        $listVal = $matchListVal[0];

        array_walk($listIds, function (&$v, $k) {
            $v = strtolower(str_replace('=', '', $v));
        });

        array_walk($listVal, function (&$v, $k) {
            $v = preg_replace('/=|"/', '', $v);
        });
        $serverInfo = array_combine($listIds, $listVal);

        if (is_array($serverInfo) && count($serverInfo) > 0) {
            return $serverInfo;
        }

        $rhelOs = shell_exec("cut -f 1 -d ' ' /etc/redhat-release 2>/dev/null");
        if (!empty($rhelOs)) {
            return [
                'name'       => trim($rhelOs),
                'version_id' => trim(shell_exec('grep -o "[0-9\.]*" /etc/redhat-release |head -n1')),
            ];
        }
    }

    public function getMemoryLimit()
    {
        return ini_get('memory_limit');
    }

    public function getMemoryPeak()
    {
        return memory_get_peak_usage();
    }

    public function freeDiskSpace()
    {
        if (function_exists('disk_free_space')) {
            return disk_free_space(ABSPATH);
        } else {
            return 0;
        }
    }

    public function totalDiskSpace()
    {
        if (function_exists('disk_total_space')) {
            return disk_total_space(ABSPATH);
        } else {
            return 0;
        }
    }

    private function fixSchema($url)
    {
        $siteUrl = get_option('siteurl');
        if (strpos($siteUrl, 'https://') !== false) {
            $schema = 'https://';
        } else {
            $schema = 'http://';
        }

        if (strpos($url, 'https://') !== false) {
            $url = str_replace('https://', $schema, $url);
        } elseif (strpos($url, 'http://') !== false) {
            $url = str_replace('http://', $schema, $url);
        } else {
            $url = $schema.$url;
        }

        return $url;
    }

    public function bytesToHuman($bytes)
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function getHandler()
    {
        return php_sapi_name();
    }

    public function getServerType()
    {
        if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
            return 'apache';
        }
        if (stripos($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false || $sapi == 'litespeed') {
            return 'litespeed';
        }
        if (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
            return 'nginx';
        }
        if (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false) {
            return 'iis';
        }
    }

    /**
     * @return bool
     */
    public function isApacheModPHP()
    {
        return $this->getServerType() == 'apache' && function_exists('apache_get_modules');
    }

    /**
     * Not sure if this can be implemented at the PHP level.
     *
     * @return bool
     */
    public function isApacheSuPHP()
    {
        return $this->getServerType() == 'apache' &&
            $this->isCGI() &&
            function_exists('posix_getuid') &&
            getmyuid() === posix_getuid();
    }

    /**
     * @return bool
     */
    public function isCGI()
    {
        return !$this->isFastCGI() && stripos($this->getHandler(), 'cgi') !== false;
    }

    /**
     * @return bool
     */
    public function isFastCGI()
    {
        return stripos($this->getHandler(), 'fastcgi') !== false || stripos($this->getHandler(), 'fpm-fcgi') !== false;
    }
}
