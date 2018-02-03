<?php

class WP_MadeIT_Security_Firewall
{
    private $defaultSettings = [];
    private $settings;
    private $db;

    public function __construct($settings, $db)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
        $this->db = $db;
    }
    
    public function show()
    {
        if (!$this->defaultSettings['firewall']['enabled']) {
            list($optionFound, $option, $optionName, $backupFiles, $currentAutoPrependFile, $autoPrependHelper) = $this->getFirewallServerConfigs();
            if(isset($_GET['firewallnonce']) && isset($_GET['action']) && wp_verify_nonce($_GET['firewallnonce'], 'madeit_security_firewall_install') && $_GET['action'] == 'installFirewall') {
                $result = $this->installFirewall($optionFound, $option, $optionName, $backupFiles, $currentAutoPrependFile, $autoPrependHelper);
                if($result === true) {
                    $successInstall = true;
                }
                else {
                    $error = $result;
                }
            }
        }
        else {
            if(defined('MADEIT_SECURITY_FIREWALL_ENABLED')) {
                $this->settings->updateSetting('madeit_security_firewall_enabled', true);
                $this->defaultSettings = $this->settings->loadDefaultSettings();
            }
            if(isset($_GET['firewallnonce']) && isset($_GET['action']) && wp_verify_nonce($_GET['firewallnonce'], 'madeit_security_firewall_uninstall') && $_GET['action'] == 'uninstallFirewall') {
                list($optionFound, $option, $optionName, $backupFiles, $currentAutoPrependFile, $autoPrependHelper) = $this->getFirewallServerConfigs();
                $result = $this->uninstallFirewall($optionFound, $option, $optionName, $backupFiles, $currentAutoPrependFile, $autoPrependHelper);
                if($result === true) {
                    $successUninstall = true;
                }
                else if($result === false) {
                    $successUninstall = false;
                }
                else {
                    $error = $result;
                }
            }
        }
        
        if(!isset($successInstall) && !isset($successUninstall) && !isset($error) && $this->defaultSettings['firewall']['enabled'] && !defined('MADEIT_SECURITY_FIREWALL_ENABLED')) {
            $successUninstall = true;
            $this->settings->updateSetting('madeit_security_firewall_enabled', false);
            $this->defaultSettings = $this->settings->loadDefaultSettings();
        }
        
        if(!isset($successInstall) && !isset($successUninstall) && !isset($error) && !$this->defaultSettings['firewall']['enabled'] && defined('MADEIT_SECURITY_FIREWALL_ENABLED')) {
            $successInstall = true;
            $this->settings->updateSetting('madeit_security_firewall_enabled', true);
            $this->defaultSettings = $this->settings->loadDefaultSettings();
        }
        
        
        $adminURL = network_admin_url('admin.php?page=madeit_security_firewall');
        $nonceInstall = wp_create_nonce('madeit_security_firewall_install');
        $nonceUninstall = wp_create_nonce('madeit_security_firewall_uninstall');
        include_once MADEIT_SECURITY_ADMIN.'/templates/firewall.php';
    }
    
    private function installFirewall($optionFound, $option, $optionName, $backupFiles, $currentAutoPrependFile, $autoPrependHelper)
    {
        global $wp_filesystem;
        $currentAutoPrepend = null;
        if($optionFound) {
            ob_start();
            $ajaxURL = admin_url('admin-ajax.php');
            $allow_relaxed_file_ownership = true;
            if (false === ($credentials = request_filesystem_credentials($ajaxURL, '', false, ABSPATH, array('version', 'locale', 'action', 'serverConfiguration', 'currentAutoPrepend'), $allow_relaxed_file_ownership))) {
                $credentialsContent = ob_get_clean();
                return sprintf(__('Filesystem credentials required. Once you have entered credentials, restart the setup. %s', 'wp-security-by-made-it'), $credentialsContent);
            }
            ob_end_clean();

            if (!WP_Filesystem($credentials, ABSPATH, $allow_relaxed_file_ownership) && $wp_filesystem->errors->get_error_code()) {
                $credentialsError = '';
                foreach ($wp_filesystem->errors->get_error_messages() as $message) {
                    if (is_wp_error($message)) {
                        if ($message->get_error_data() && is_string($message->get_error_data())) {
                            $message = $message->get_error_message() . ': ' . $message->get_error_data();
                        }
                        else {
                            $message = $message->get_error_message();
                        }
                    }
                    $credentialsError .= "<p>$message</p>\n";
                }
                return sprintf(__('Filesystem permission error. %s', 'wp-security-by-made-it'), $credentialsError);
            }

            try {
                $autoPrependHelper->performInstallation($option, $wp_filesystem, $currentAutoPrependFile);

                $verifyURL = add_query_arg(array('firewall_action' => 'install_status'), $ajaxURL);
                $response = wp_remote_get($verifyURL, array('headers' => array('Referer' => false)));

                $active = false;
                if (!is_wp_error($response)) {
                    $firewallStatus = @json_decode(wp_remote_retrieve_body($response), true);
                    if (isset($firewallStatus['success'])) {
                        $active = $firewallStatus['success'];
                    }
                }
                //Install completed
                $this->settings->updateSetting('madeit_security_firewall_enabled', true);
                $this->defaultSettings = $this->settings->loadDefaultSettings();
            }
            catch (Exception $e) {
                $installError = "<p>" . $e->getMessage() . "</p>";
                return sprintf(__('Installation failed. %s', 'wp-security-by-made-it'), $installError);
            }
            return true;
        }
    }
    
    private function uninstallFirewall($optionFound, $option, $optionName, $backupFiles, $currentAutoPrependFile, $autoPrependHelper)
    {
        global $wp_filesystem;
        
        $ajaxURL = admin_url('admin-ajax.php');
        $allow_relaxed_file_ownership = true;
        ob_start();
        if (false === ($credentials = request_filesystem_credentials($ajaxURL, '', false, ABSPATH, array('version', 'locale', 'action', 'serverConfiguration', 'iniModified'), $allow_relaxed_file_ownership))) {
            $credentialsContent = ob_get_clean();
            return sprintf(__('Filesystem credentials required. Once you have entered credentials, restart the setup. %s', 'wp-security-by-made-it'), $credentialsContent);
        }
        ob_end_clean();
        
        if (!WP_Filesystem($credentials, ABSPATH, $allow_relaxed_file_ownership) && $wp_filesystem->errors->get_error_code()) {
            $credentialsError = '';
            foreach ($wp_filesystem->errors->get_error_messages() as $message) {
                if (is_wp_error($message)) {
                    if ($message->get_error_data() && is_string($message->get_error_data())) {
                        $message = $message->get_error_message() . ': ' . $message->get_error_data();
                    }
                    else {
                        $message = $message->get_error_message();
                    }
                }
                $credentialsError .= "<p>$message</p>\n";
            }
            return sprintf(__('Filesystem permission error. %s', 'wp-security-by-made-it'), $credentialsError);
        }

        try {
            if ($autoPrependHelper->usesUserIni($option) && !isset($_GET['remove_ini']))
            { //Uses .user.ini but not yet modified
                $hasPreviousAutoPrepend = $autoPrependHelper->performIniRemoval($option, $wp_filesystem);
                return false;
            }
            else
            { //.user.ini modified if applicable and waiting period elapsed or otherwise ready to advance to next step
                if ($autoPrependHelper->usesUserIni($option) && MADEIT_SECURITY_FIREWALL_ENABLED) { //.user.ini modified, but the firewall is still enabled
                    return __('The Firewall has not been disabled. This may be because <code>auto_prepend_file</code> is configured somewhere else or the value is still cached by PHP.', 'wp-security-by-made-it');
                }
                else if (!$autoPrependHelper->usesUserIni($option)) {
                    $autoPrependHelper->performIniRemoval($option, $wp_filesystem); //Do .htaccess here
                }

                $autoPrependHelper->performAutoPrependFileRemoval($wp_filesystem);

                $this->settings->updateSetting('madeit_security_firewall_enabled', false);
                $this->defaultSettings = $this->settings->loadDefaultSettings();
            }
        }
        catch (Exception $e) {
            $installError = "<p>" . $e->getMessage() . "</p>";
            return sprintf(__('Installation failed. %s', 'wp-security-by-made-it'), $installError);
        }
        return true;
    }
    
    private function getFirewallServerConfigs()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_SystemInfo.php';
        require_once MADEIT_SECURITY_DIR.'/inc/firewall/WP_MadeIT_Security_AutoPrependHelper.php';
        $autoPrependHelper = new WP_MadeIT_Security_AutoPrependHelper();
        $systeminfo = new WP_MadeIT_Security_SystemInfo();
        
        $serverType = $systeminfo->getServerType();
        
        
        $optionFound = false;
        $option = "";
        $optionName = "";
        $backupFiles = array();
        $currentAutoPrependFile = ini_get('auto_prepend_file');
        if($systeminfo->isApacheModPHP())
        {
            $option = "apache-mod_php";
            $optionFound = true;
            $backupFiles = $autoPrependHelper->getFilesNeededForBackup('apache-mod_php');
            $optionName = __('Apache + mod_php', 'wp-security-by-made-it');
        }
        elseif($systeminfo->isApacheSuPHP())
        {
            $option = "apache-suphp";
            $optionFound = true;
            $backupFiles = $autoPrependHelper->getFilesNeededForBackup('apache-suphp');
            $optionName = __('Apache + suPHP', 'wp-security-by-made-it');
        }
        elseif($serverType == 'apache' && !$systeminfo->isApacheSuPHP() && ($systeminfo->isCGI() || $systeminfo->isFastCGI()))
        {
            $option = "cgi";
            $optionFound = true;
            $backupFiles = $autoPrependHelper->getFilesNeededForBackup('cgi');
            $optionName = __('Apache + CGI/FastCGI', 'wp-security-by-made-it');
        }
        elseif($serverType == 'litespeed')
        {
            $option = "litespeed";
            $optionFound = true;
            $backupFiles = $autoPrependHelper->getFilesNeededForBackup('litespeed');
            $optionName = __('LiteSpeed/lsapi', 'wp-security-by-made-it');
        }
        elseif($serverType == 'nginx')
        {
            $option = "nginx";
            $optionFound = true;
            $backupFiles = $autoPrependHelper->getFilesNeededForBackup('nginx');
            $optionName = __('NGINX', 'wp-security-by-made-it');
        }
        elseif($serverType == 'iis')
        {
            $option = "iis";
            $optionFound = true;
            $backupFiles = $autoPrependHelper->getFilesNeededForBackup('iis');
            $optionName = __('Windows (IIS)', 'wp-security-by-made-it');
        }
        return array(
            $optionFound,
            $option,
            $optionName,
            $backupFiles,
            $currentAutoPrependFile,
            $autoPrependHelper
        );
    }
    
    public function addHooks()
    {
        add_action('admin_init', function() {
            if(isset($_GET['downloadBackup']) && isset($_GET['backupIndex']) && isset($_GET['firewallnonce']) && isset($_GET['action'])) {
                if(wp_verify_nonce($_GET['firewallnonce'], 'madeit_security_firewall_backup')) {
                    if($_GET['action'] == 'downloadConfigBackup' && $_GET['downloadBackup'] == 1) {
                        //download file
                        list($optionFound, $option, $optionName, $backupFiles, $currentAutoPrependFile, $autoPrependHelper) = $this->getFirewallServerConfigs();
                        
                        $autoPrependHelper->downloadBackups($option, $_GET['backupIndex']);
                    }
                }
            }
        });
    }
}
