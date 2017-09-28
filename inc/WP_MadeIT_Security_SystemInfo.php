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
        preg_match('/[0-9\.]+/', apache_get_version(), $matches);

        return $matches[0];
    }

    public function getHomeUrl()
    {
        if (is_multisite()) {
            return network_site_url();
        }

        return home_url('/');
    }

    public function getAdminUrl()
    {
        return get_admin_url();
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

    public function getSystemInfo()
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
            'php_version'    => $php_version,
            'mysql_version'  => $mysql_version,
            'wp_version'     => $wp_version,
            'apache_version' => $apache_version,
            'url'            => $wp_install,
            'admin_url'      => $wp_admin_url,
            'user_count'     => $user_count,
            'site_count'     => $num_blogs,
            'path'           => $path,
            'os_name'        => isset($osInfo['name']) ? $osInfo['name'] : null,
            'os_version'     => isset($osInfo['version_id']) ? $osInfo['version_id'] : null,
        ];

        return $systeminfo;
    }

    private function getOSInformation()
    {
        if (false == function_exists('shell_exec')) {
            return;
        }

        //Check Ubuntu
        $os = shell_exec('cat /etc/os-release');
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

        $rhelOs = shell_exec("cut -f 1 -d ' ' /etc/redhat-release");
        if (!empty($rhelOs)) {
            return [
                'name'       => trim($rhelOs),
                'version_id' => trim(shell_exec('grep -o "[0-9\.]*" /etc/redhat-release |head -n1')),
            ];
        }
    }
}
