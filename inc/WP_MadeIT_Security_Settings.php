<?php

class WP_MadeIT_Security_Settings
{
    private $defaultSettings = [];

    public function __construct()
    {
        $this->loadDefaultSettings();
    }

    public function loadDefaultSettings()
    {
        if (trim(get_option('madeit_security_api_key', '')) == '' && MADEIT_SECURITY_API == false) {
            @define('MADEIT_SECURITY_API', true);
            update_option('madeit_security_api_key', $this->fetchNewApiKey());
        }

        $this->defaultSettings = [
            'scan' => [
                'fast' => get_option('madeit_security_scan_repo_fast', false),
                'repo' => [
                    'core'   => get_option('madeit_security_scan_repo_core', true),
                    'theme'  => get_option('madeit_security_scan_repo_theme', true),
                    'plugin' => get_option('madeit_security_scan_repo_plugin', true),
                ],
                'update' => get_option('madeit_security_scan_update', true),
            ],
            'api' => [
                'key' => get_option('madeit_security_api_key', ''),
            ],
            'maintenance' => [
                'enable' => get_option('madeit_security_maintenance_enable', false),
                'key'    => get_option('madeit_security_maintenance_api_key', ''),
                'backup' => get_option('madeit_security_maintenance_backup', false),
            ],
            'backup' => [
                'enabled' => get_option('madeit_security_backup_enabled', 500),
                'files' => get_option('madeit_security_backup_files', 500),
                'ftp'   => [
                    'enabled'         => get_option('madeit_security_backup_ftp_enable', false),
                    'server'          => get_option('madeit_security_backup_ftp_server', ''),
                    'username'        => get_option('madeit_security_backup_ftp_username', ''),
                    'password'        => get_option('madeit_security_backup_ftp_password', ''),
                    'destination_dir' => get_option('madeit_security_backup_ftp_destination_directory', ''),
                ],
                's3' => [
                    'enabled'     => get_option('madeit_security_backup_s3_enable', false),
                    'access_key'  => get_option('madeit_security_backup_s3_access_key', ''),
                    'secret_key'  => get_option('madeit_security_backup_s3_secret_key', ''),
                    'bucket_name' => get_option('madeit_security_backup_s3_bucket_name', ''),
                ],
            ],
            'firewall' => [
                'enabled' => get_option('madeit_security_firewall_enabled', false),
                'login'   => [
                    'attempts_delay_time'             => get_option('madeit_security_firewall_login_attempts_delay_time', 60 * 15), //15 minutes
                    'attempts_failed'                 => get_option('madeit_security_firewall_login_attempts_failed', 5),
                    'attempts_block_time'             => get_option('madeit_security_firewall_login_attempts_block_time', 60 * 60), //1uur
                    'attempts_block_wrong_user'       => get_option('madeit_security_firewall_login_attempts_block_wrong_user', true),
                    'attempts_block_wrong_user_count' => get_option('madeit_security_firewall_login_attempts_block_wrong_user_count', 2),
                ],
            ],
            'report' => [
                'weekly' => [
                    'enabled' => get_option('madeit_security_report_weekly_enabled', false),
                    'email' => get_option('madeit_security_report_weekly_email', get_option('admin_email')),
                ]
            ]
        ];

        return $this->defaultSettings;
    }

    private function fetchNewApiKey()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_SystemInfo.php';

        $systemInfo = new WP_MadeIT_Security_SystemInfo();

        //Info
        $data = $systemInfo->getSystemInfo();
        unset($data['path']);
        unset($data['apache_version']);
        unset($data['user_count']);
        unset($data['site_count']);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/get-key');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept' => 'application/json']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);
        $result = json_decode($server_output, true);

        return isset($result['key']) ? $result['key'] : '';
    }

    public function checkCheckbox($key)
    {
        if (isset($_POST[$key]) && $_POST[$key] == 1) {
            update_option($key, true);
        } else {
            update_option($key, false);
        }
    }

    public function checkTextbox($key)
    {
        if (isset($_POST[$key])) {
            update_option($key, sanitize_text_field($_POST[$key]));
        } else {
            update_option($key, '');
        }
    }

    public function checkNumeric($key)
    {
        if (isset($_POST[$key]) && is_numeric(sanitize_text_field($_POST[$key]))) {
            update_option($key, sanitize_text_field($_POST[$key]));
        } else {
            update_option($key, '');
        }
    }

    public function checkApiKey($key)
    {
        $url = 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/website/'.$key;
        $response = wp_remote_get($url);
        $content = wp_remote_retrieve_body($response);
        $json = json_decode($content, true);

        return $json;
    }

    public function updateSetting($key, $value)
    {
        update_option($key, $value);
    }

    public function createLoggingDir()
    {
        $dir = WP_CONTENT_DIR.'/madeit-security-backup';
        $madeitIps = [
            '2a02:7b40:b945:36e5::1', //s1
            '185.69.54.229', //s1
            '2a02:7b40:5eb0:ef7f::1', //s2
            '94.176.239.127',
            '209.250.249.53', //s3
            '2001:19f0:5001:722:5400:1ff:fe55:d9b2',
            '167.99.222.145', //s5 new
            '2a03:b0c0:2:d0::c03:2001', //s5 new
        ];

        $correctHtAccessContent = "order deny,allow\ndeny from all\n";
        foreach ($madeitIps as $ip) {
            $correctHtAccessContent .= "allow from $ip\n";
        }

        // Check for the existence of the dir and prevent enumeration
        // index.php is for a sanity check - make sure that we're not somewhere unexpected
        if ((!is_dir($dir) || !is_file($dir.'/index.html') || !is_file($dir.'/.htaccess')) && !is_file($dir.'/index.php') || !is_file($dir.'/web.config') || !is_file($dir.'/error.log')) {
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($dir.'/index.html', '<html><body><a href="https://www.madeit.be">WordPress backups by Security by Made I.T.</a></body></html>');
            if (!is_file($dir.'/.htaccess')) {
                file_put_contents($dir.'/.htaccess', $correctHtAccessContent);
            }
            if (!is_file($dir.'/web.config')) {
                file_put_contents($dir.'/web.config', "<configuration>\n<system.webServer>\n<authorization>\n<deny users=\"*\" />\n</authorization>\n</system.webServer>\n</configuration>\n");
            }
            if (!is_file($dir.'/error.log')) {
                file_put_contents($dir.'/error.log', '');
            }
        } else {
            $htaccessContent = file_get_contents($dir.'/.htaccess');
            foreach ($madeitIps as $ip) {
                if (strpos($htaccessContent, $ip) === false) {
                    file_put_contents($dir.'/.htaccess', $correctHtAccessContent);

                    return $dir;
                }
            }
        }

        return $dir;
    }

    public function saveConfigs($load = false)
    {
        $dir = $this->createLoggingDir();
        if (count($this->defaultSettings) == 0) {
            $this->loadDefaultSettings();
        }

        if ($load && file_exists($dir.'/wp-security-config.php')) {
            return;
        }

        $content = "<?php\n";
        $content .= "//WP Security By Made I.T. Configs\n";
        $content .= "\$wp_security_by_madeit_configs = json_decode('".json_encode($this->defaultSettings)."', true);\n";
        file_put_contents($dir.'/wp-security-config.php', $content);
    }
}
