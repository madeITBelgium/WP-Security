<?php
class WP_MadeIT_Security_Settings {
    private $defaultSettings = array();

    function __construct() {
        $this->loadDefaultSettings();
    }
    
    public function loadDefaultSettings() {
        if(get_option('madeit_security_scan_repo_fast', null) === null) {
            update_option('madeit_security_scan_repo_fast', false);
        }
        if(get_option('madeit_security_scan_repo_core', null) === null) {
            update_option('madeit_security_scan_repo_core', true);
        }
        if(get_option('madeit_security_scan_repo_theme', null) === null) {
            update_option('madeit_security_scan_repo_theme', true);
        }
        if(get_option('madeit_security_scan_repo_plugin', null) === null) {
            update_option('madeit_security_scan_repo_plugin', true);
        }
        if(get_option('madeit_security_scan_update', null) === null) {
            update_option('madeit_security_scan_update', true);
        }
        if(get_option('madeit_security_maintenance_api_key', null) === null) {
            update_option('madeit_security_maintenance_api_key', '');
        }
        if(get_option('madeit_security_api_key', null) === null) {
            update_option('madeit_security_api_key', '');
        }
        if(get_option('madeit_security_maintenance_enable', null) === null) {
            update_option('madeit_security_maintenance_enable', false);
        }
        if(get_option('madeit_security_maintenance_backup', null) === null) {
            update_option('madeit_security_maintenance_backup', false);
        }
        
        if(trim(get_option('madeit_security_api_key', '')) == '' && MADEIT_SECURITY_API == false) {
            define('MADEIT_SECURITY_API', true);
            update_option('madeit_security_api_key', $this->fetchNewApiKey());
        }
        
        $this->defaultSettings = [
            'scan' => [
                'fast' => get_option('madeit_security_scan_repo_fast', false),
                'repo' => [
                    'core' => get_option('madeit_security_scan_repo_core', false),
                    'theme' => get_option('madeit_security_scan_repo_theme', false),
                    'plugin' => get_option('madeit_security_scan_repo_plugin', false),
                ],
                'update' => get_option('madeit_security_scan_update', false),
            ],
            'api' => [
                'key' => get_option('madeit_security_api_key', ''),
            ],
            'maintenance' => [
                'enable' => get_option('madeit_security_maintenance_enable', false),
                'key' => get_option('madeit_security_maintenance_api_key', ''),
                'backup' => get_option('madeit_security_maintenance_backup', false),
            ],
        ];
        return $this->defaultSettings;
    }
    
    private function fetchNewApiKey() {
        require_once(MADEIT_SECURITY_DIR . '/inc/WP_MadeIT_Security_SystemInfo.php');
        
        $systemInfo = new WP_MadeIT_Security_SystemInfo();

        //Info
        $data = $systemInfo->getSystemInfo();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/get-key');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept' => 'application/json']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        curl_close ($ch);
        $result = json_decode($server_output, true);
        print_r($result);
        return isset($result['key']) ? $result['key'] : "";
    }
    
    public function checkCheckbox($key) {
        if(isset($_POST[$key]) && $_POST[$key] == 1) {
            update_option($key, true);
        }
        else {
            update_option($key, false);
        }
    }
    
    public function checkTextbox($key) {
        if(isset($_POST[$key])) {
            update_option($key, $_POST[$key]);
        }
        else {
            update_option($key, "");
        }
    }
    
    public function checkApiKey($key) {
        $content = file_get_contents('https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/website/' . $key);
        $json = json_decode($content, true);
        return $json;
    }
}