<?php

class WP_MadeIT_Security_Admin
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

    private function getAlertCount()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';

        $plugins = new WP_MadeIT_Security_Plugin();
        $core = new WP_MadeIT_Security_Core();
        $themes = new WP_MadeIT_Security_Theme();

        $count = 0;
        $errors = $this->db->querySingleRecord("SELECT count(*) as aantal FROM `" . $this->db->prefix() . "madeit_sec_filelist` WHERE reason IS NOT NULL AND `ignore` != 1");
        if(isset($errors['aantal'])) {
            $count = $errors['aantal'];
        }

        return $plugins->countUpdates(false) + $themes->countUpdates(false) + ($core->hasUpdate() ? 1 : 0) + $count;
    }

    public function initMenu()
    {
        $new = '';
        $alerts = $this->getAlertCount();
        if ($alerts > 0) {
            $new = "<span class='update-plugins'><span class='update-count'>".number_format_i18n($alerts).'</span></span>';
        }
        add_menu_page(__('Security & Maintenance', 'wp-security-by-made-it'), __('Security', 'wp-security-by-made-it').' '.$new, 'manage_options', 'madeit_security', [$this, 'show_dashboard'], MADEIT_SECURITY_URL.'assets/icon-16x16.png', 9999);
        add_submenu_page('madeit_security', __('Security Dashboard', 'wp-security-by-made-it'), __('Dashboard', 'wp-security-by-made-it'), 'manage_options', 'madeit_security', [$this, 'show_dashboard']);
        add_submenu_page('madeit_security', __('Security Scan', 'wp-security-by-made-it'), __('Scan', 'wp-security-by-made-it'), 'manage_options', 'madeit_security_scan', [$this, 'show_scan']);
        add_submenu_page('madeit_security', __('Security Settings', 'wp-security-by-made-it'), __('Settings', 'wp-security-by-made-it'), 'manage_options', 'madeit_security_settings', [$this, 'settings']);
    }

    public function initStyle()
    {
        wp_register_style('madeit-security-admin-style', MADEIT_SECURITY_URL.'/admin/css/style.css', [], null);
        wp_enqueue_style('madeit-security-admin-style');

        wp_register_style('madeit-tabs', MADEIT_SECURITY_URL.'/admin/css/tabs.css', [], null);
        wp_enqueue_style('madeit-tabs');
        wp_register_style('madeit-grid', MADEIT_SECURITY_URL.'/admin/css/grid.css', [], null);
        wp_enqueue_style('madeit-grid');
        wp_register_style('madeit-card', MADEIT_SECURITY_URL.'/admin/css/card.css', [], null);
        wp_enqueue_style('madeit-card');

        wp_register_style('font-awesome', MADEIT_SECURITY_URL.'/admin/css/font-awesome.min.css', [], null);
        wp_enqueue_style('font-awesome');

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');

        wp_enqueue_script('madeit-security-script', MADEIT_SECURITY_URL.'/admin/js/script.js', ['jquery'], 1, true);
        wp_enqueue_script('madeit-tabs', MADEIT_SECURITY_URL.'/admin/js/tabs.js', ['jquery'], 1, true);
    }

    public function settings()
    {
        $success = false;
        $error = '';
        if (isset($_POST['save_settings'])) {
            $success = $this->save_settings();
            if ($success !== true) {
                $error = $success;
                $success = false;
            }
        }
        include_once MADEIT_SECURITY_ADMIN.'/templates/settings.php';
    }

    private function save_settings()
    {
        $success = false;
        $nonce = $_POST['_wpnonce'];
        if (!wp_verify_nonce($nonce, 'madeit_security_settings')) {
            // This nonce is not valid.
            wp_die('Security check');
        } else {
            //$this->settings->checkTextbox('madeit_security_api_key');

            //Check API Key
            $newKey = sanitize_text_field($_POST['madeit_security_maintenance_api_key']);
            $checkApiKey = $this->settings->checkApiKey($newKey);
            if (!isset($checkApiKey['success']) || (isset($checkApiKey['success']) && !$checkApiKey['success'])) {
                update_option('madeit_security_maintenance_api_key', '');
                update_option('madeit_security_maintenance_enable', false);
                $this->defaultSettings = $this->settings->loadDefaultSettings();

                return 'The provided API Key is invalid.';
            } elseif (isset($checkApiKey['success']) && $checkApiKey['success']) {
                update_option('madeit_security_maintenance_api_key', $newKey);
                update_option('madeit_security_api_key', $newKey);
                update_option('madeit_security_maintenance_enable', true);
            }

            //FTP settings
            $ftp = false;
            if (isset($_POST['madeit_security_backup_ftp_enable'])) {
                $ftp_username = sanitize_text_field($_POST['madeit_security_backup_ftp_username']);
                $ftp_password = sanitize_text_field($_POST['madeit_security_backup_ftp_password']);
                $ftp_server = sanitize_text_field($_POST['madeit_security_backup_ftp_server']);
                $destination = sanitize_text_field($_POST['madeit_security_backup_ftp_destination_directory']);

                $conn_id = ftp_connect($ftp_server);
                if ($conn_id === false) {
                    return 'Cannot connect to the FTP server.';
                }
                $login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
                if ($login_result === false) {
                    return 'FTP credentials are wrong.';
                }
                $ftp = true;
            }

            //S3 settings
            $s3 = false;
            if (isset($_POST['madeit_security_backup_s3_enable'])) {
                $awsAccessKey = sanitize_text_field($_POST['madeit_security_backup_s3_access_key']);
                $awsSecretKey = sanitize_text_field($_POST['madeit_security_backup_s3_secret_key']);
                $bucketName = sanitize_text_field($_POST['madeit_security_backup_s3_bucket_name']);

                require_once MADEIT_SECURITY_DIR.'/inc/backup/WP_MadeIT_Security_S3.php';
                $s3 = new WP_MadeIT_Security_S3($awsAccessKey, $awsSecretKey);

                $error = false;

                try {
                    if ($s3->getBucket($bucketName) === false) {
                        $error = true;
                    }
                } catch (Exception $e) {
                    return $e->getMessage();
                }
                if ($error) {
                    return 'Cannot connect to S3 bucket';
                }

                //check if S3 is available
                $s3 = true;
            }

            //Backup settings
            update_option('madeit_security_backup_ftp_enable', $ftp);
            $this->settings->checkTextbox('madeit_security_backup_ftp_username');
            $this->settings->checkTextbox('madeit_security_backup_ftp_password');
            $this->settings->checkTextbox('madeit_security_backup_ftp_server');
            $this->settings->checkTextbox('madeit_security_backup_ftp_destination_directory');
            update_option('madeit_security_backup_s3_enable', $s3);
            $this->settings->checkTextbox('madeit_security_backup_s3_access_key');
            $this->settings->checkTextbox('madeit_security_backup_s3_secret_key');
            $this->settings->checkTextbox('madeit_security_backup_s3_bucket_name');

            //General settings
            $this->settings->checkCheckbox('madeit_security_scan_repo_fast');
            $this->settings->checkCheckbox('madeit_security_scan_repo_core');
            $this->settings->checkCheckbox('madeit_security_scan_repo_theme');
            $this->settings->checkCheckbox('madeit_security_scan_repo_plugin');

            //Maintenance settings
            $this->settings->checkCheckbox('madeit_security_scan_update');
            $this->settings->checkCheckbox('madeit_security_maintenance_backup');

            $this->defaultSettings = $this->settings->loadDefaultSettings();

            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Maintenance.php';
            $wp_maintenance = new WP_MadeIT_Security_Maintenance($this->settings);
            $wp_maintenance->setUp();

            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Update.php';
            $wpNotify = new WP_MadeIT_Security_Update($this->settings);
            if ($this->defaultSettings['maintenance']['enable'] === true || $this->defaultSettings['scan']['update']) {
                $wpNotify->activateSechduler(false);
            } else {
                $wpNotify->activateSechduler(true);
            }

            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup.php';
            $wpBackup = new WP_MadeIT_Security_Backup($this->settings);
            if ($this->defaultSettings['maintenance']['backup'] === true || $this->defaultSettings['backup']['ftp']['enabled'] || $this->defaultSettings['backup']['s3']['enabled']) {
                $wpBackup->activateSechduler(false);
            } else {
                $wpBackup->activateSechduler(true);
            }

            $success = true;
        }

        return $success;
    }

    public function show_dashboard()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';

        $repoScanData = get_site_transient('madeit_security_repo_scan');
        $updateScanData = get_site_transient('madeit_security_update_scan');
        $backupExecutionData = get_site_transient('madeit_security_backup');

        include_once MADEIT_SECURITY_ADMIN.'/templates/dashboard.php';
    }

    public function show_scan()
    {
        if (isset($_GET['changes'])) {
            $this->showChanges();
        } elseif (isset($_GET['notexist'])) {
            $this->notExist();
        } else {
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';
            
            $lastScan = get_site_transient('madeit_security_scan');
            $plugins = $this->db->querySelect("SELECT * FROM `" . $this->db->prefix() . "madeit_sec_filelist` WHERE plugin_file = 1 AND reason IS NOT NULL AND `ignore` != 1 ORDER BY `plugin_theme` ASC");
            $pluginScanData = [];
            $plugin = "";
            foreach($plugins as $value) {
                if($plugin != $value['plugin_theme']) {
                    $plugin = $value['plugin_theme'];
                    $pluginScanData[$plugin] = [];
                }
                if(!isset($pluginScanData[$plugin][$value['reason']])) {
                    $pluginScanData[$plugin][$value['reason']] = [];
                }
                $pluginScanData[$plugin][$value['reason']][] = $value['filename'];
            }
            
            
            $themes = $this->db->querySelect("SELECT * FROM `" . $this->db->prefix() . "madeit_sec_filelist` WHERE theme_file = 1 AND reason IS NOT NULL AND `ignore` != 1 ORDER BY `plugin_theme` ASC");
            $themeScanData = [];
            $theme = "";
            foreach($themes as $value) {
                if($theme != $value['plugin_theme']) {
                    $theme = $value['plugin_theme'];
                    $themeScanData[$plugin] = [];
                }
                if(!isset($themeScanData[$plugin][$value['reason']])) {
                    $themeScanData[$plugin][$value['reason']] = [];
                }
                $themeScanData[$plugin][$value['reason']][] = $value['filename'];
            }
            
            
            $updateScanData = get_site_transient('madeit_security_update_scan');

            include_once MADEIT_SECURITY_ADMIN.'/templates/scan.php';
        }
    }

    private function showChanges()
    {
        $plugin = sanitize_text_field($_GET['changes']);
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        $wp_plugin = new WP_MadeIT_Security_Plugin();
        $pluginsData = $wp_plugin->getPlugins();
        $version = '';
        $path = WP_PLUGIN_DIR;
        foreach ($pluginsData as $key => $pluginData) {
            if ($pluginData['slug'] == $plugin) {
                $version = $pluginData['version'];
                $path .= '/'.substr($key, 0, strpos($key, '/'));
            }
        }
        $list = true;
        if (isset($_GET['ignore_all'])) {
            $nonce = sanitize_text_field($_GET['ignore_all']);
            if (!wp_verify_nonce($nonce, 'madeit_security_ignore_file')) {
                // This nonce is not valid.
                wp_die('Security check');
            } else {
                $this->ignoreAll($plugin);
            }
        }
        if (isset($_GET['file']) && strlen($version) > 2) {
            $file = sanitize_text_field($_GET['file']);
            $fileData = $this->db->querySingleRecord("SELECT * FROM `" . $this->db->prefix() . "madeit_sec_filelist` WHERE filename_md5 = %s", $file);
            
            $localFile = ABSPATH.$fileData['filename'];
            
            $pluginName = $fileData['plugin_theme'];
            $startDir = WP_PLUGIN_DIR;
            if(strpos($pluginName, '/') > 0) {
                $pluginDir = str_replace(ABSPATH, '', $startDir.'/'.substr($pluginName, 0, strpos($pluginName, '/'))) . "/";
            }
            else {
                $pluginDir = str_replace(ABSPATH, '', $startDir.'/'.$pluginName) . "/";
            }
            $fileName = preg_replace('/' . preg_quote($pluginDir, '/') . '/', '', $fileData['filename'], 1);
            
            
            $error = null;
            $list = false;
            if (!is_file($localFile) || strpos($file, '../') === true) {
                $error = sprintf(__('Local file %s doesn\'t exist on your WordPress installation.', 'wp-security-by-made-it'), $file);
            } else {
                if (isset($_GET['ignore'])) {
                    $nonce = sanitize_text_field($_GET['ignore']);
                    if (!wp_verify_nonce($nonce, 'madeit_security_ignore_file')) {
                        // This nonce is not valid.
                        wp_die('Security check');
                    } else {
                        $this->ignoreFile($plugin, $file);
                        $list = true;
                    }
                } elseif (isset($_GET['deignore'])) {
                    $nonce = sanitize_text_field($_GET['deignore']);
                    if (!wp_verify_nonce($nonce, 'madeit_security_ignore_file')) {
                        // This nonce is not valid.
                        wp_die('Security check');
                    } else {
                        $this->disIgnoreFile($plugin, $file);
                        $list = true;
                    }
                } elseif (isset($_GET['replace'])) {
                    //Replace the current file with the original
                    $nonce = sanitize_text_field($_GET['replace']);
                    if (!wp_verify_nonce($nonce, 'madeit_security_replace_file')) {
                        // This nonce is not valid.
                        wp_die('Security check');
                    } else {
                        $this->disIgnoreFile($plugin, $file);
                        $this->replace($plugin, $fileName, $localFile, $version);
                        $list = true;
                        $fileReplacedSuccesfull = $file;
                    }
                } else {
                    if (true) { //Use Made I.T. Cache to not overlode WP repo. TODO: make setting for this.
                        $remoteUrl = 'https://madeit.be/wordpress-onderhoud/api/1.0/wp/plugin/'.$plugin.'/getFile?version='.$version.'&file='.$fileName;
                    } else {
                        $remoteUrl = 'https://plugins.trac.wordpress.org/browser/'.$plugin.'/tags/'.$version.'/'.$fileName.'?format=txt';
                    }
                    $a = explode("\n", file_get_contents($localFile));
                    $b = explode("\n", file_get_contents($remoteUrl));
                    if (!class_exists('Diff')) {
                        require_once MADEIT_SECURITY_DIR.'/inc/compare/Diff.php';
                    }
                    $diff = new Diff($a, $b, []);

                    if (!class_exists('Diff_Renderer_Html_SideBySide')) {
                        require_once MADEIT_SECURITY_DIR.'/inc/compare/Diff/Renderer/Html/SideBySide.php';
                    }
                    $renderer = new Diff_Renderer_Html_SideBySide();
                }
            }
            if (!$list) {
                $nonce = wp_create_nonce('madeit_security_ignore_file');
                $nonceReplace = wp_create_nonce('madeit_security_replace_file');
                include_once MADEIT_SECURITY_ADMIN.'/templates/compare_files.php';
            }
        }
        if ($list) {
            $nonce = wp_create_nonce('madeit_security_ignore_file');
            
            $files = $this->db->querySelect("SELECT * FROM `" . $this->db->prefix() . "madeit_sec_filelist` WHERE plugin_file = 1 AND reason IS NOT NULL AND `ignore` != 1 AND plugin_theme = %s", $plugin);
            include_once MADEIT_SECURITY_ADMIN.'/templates/list-changed-files.php';
        }
    }
    
    private function notExist()
    {
        $plugin = sanitize_text_field($_GET['notexist']);
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        $wp_plugin = new WP_MadeIT_Security_Plugin();
        $pluginsData = $wp_plugin->getPlugins();
        $version = '';
        $path = WP_PLUGIN_DIR;
        foreach ($pluginsData as $key => $pluginData) {
            if ($pluginData['slug'] == $plugin) {
                $version = $pluginData['version'];
                $path .= '/'.substr($key, 0, strpos($key, '/'));
            }
        }
        $nonce = wp_create_nonce('madeit_security_ignore_file');

        $files = $this->db->querySelect("SELECT * FROM `" . $this->db->prefix() . "madeit_sec_filelist` WHERE plugin_file = 1 AND reason = 'File not exist in repo' AND `ignore` != 1 AND plugin_theme = %s", $plugin);
        include_once MADEIT_SECURITY_ADMIN.'/templates/list-not-exist-files.php';
    }

    private function timeAgo($ptime)
    {
        $etime = time() - $ptime;

        if ($etime < 1) {
            return __('less than 1 second', 'wp-security-by-made-it');
        }

        $a = [12 * 30 * 24 * 60 * 60 => __('year', 'wp-security-by-made-it'),
                   30 * 24 * 60 * 60 => __('month', 'wp-security-by-made-it'),
                   24 * 60 * 60      => __('day', 'wp-security-by-made-it'),
                   60 * 60           => __('hour', 'wp-security-by-made-it'),
                   60                => __('minute', 'wp-security-by-made-it'),
                   1                 => __('second', 'wp-security-by-made-it'),
        ];

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);

                return $r.' '.$str.($r > 1 ? 's' : '');
            }
        }
    }

    private function ignoreFile($plugin, $file)
    {
        $this->db->queryWrite("UPDATE " . $this->db->prefix() . "madeit_sec_filelist SET `ignore` = 1 WHERE reason IS NOT NULL AND filename_md5 = %s AND plugin_theme = %s", $file, $plugin);
    }

    private function ignoreAll($plugin)
    {
        $this->db->queryWrite("UPDATE " . $this->db->prefix() . "madeit_sec_filelist SET `ignore` = 1 WHERE reason IS NOT NULL AND plugin_theme = %s", $plugin);
    }

    private function disIgnoreFile($plugin, $file)
    {
        $this->db->queryWrite("UPDATE " . $this->db->prefix() . "madeit_sec_filelist SET `ignore` = 0 WHERE reason IS NOT NULL AND filename_md5 = %s AND plugin_theme = %s", $file, $plugin);
    }

    private function replace($plugin, $file, $localFile, $version)
    {
        if (false) { //Use Made I.T. Cache to not overlode WP repo. TODO: make setting for this.
            $remoteUrl = 'https://madeit.be/wordpress-onderhoud/api/1.0/wp/plugin/'.$plugin.'/getFile?version='.$version.'&file='.$file;
        } else {
            $remoteUrl = 'https://plugins.trac.wordpress.org/browser/'.$plugin.'/tags/'.$version.'/'.$file.'?format=txt';
        }
        $fileContent = file_get_contents($remoteUrl);

        file_put_contents($localFile, $fileContent);
    }
    
    public function doFileScan()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_LoadFiles.php';
        $scan = new WP_MadeIT_Security_LoadFiles($this->db);
        $scan->startLoadingFiles();
        echo json_encode(['success' => true]);
        wp_die();
    }
    
    public function stopFileScan()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_LoadFiles.php';
        $scan = new WP_MadeIT_Security_LoadFiles($this->db);
        $scan->stopLoadingFiles();
        echo json_encode(['success' => true]);
        wp_die();
    }
    
    public function checkFileScan()
    {
        $result = get_site_transient('madeit_security_scan');
        
        if($result === false) {
            $data = ['success' => true, 'completed' => false, 'running' => false];
        }
        else {
            $lastTimeAgo = "";
            if(isset($result['last_com_time'])) {
                $lastTimeAgo = sprintf(esc_html(__('Last result %s ago.', 'wp-security-by-made-it')), $this->timeAgo($result['last_com_time']));
            }
            $data = [
                'success' => true, 
                'completed' => $result['done'],
                'running' => !$result['done'] && !$result['stop'],
                'result' => $result,
                'time_ago' => sprintf(esc_html(__('Last scan %s ago.', 'wp-security-by-made-it')), $this->timeAgo($result['start_time'])),
                'last_time_ago' => $lastTimeAgo,
            ];
        }
        
        echo json_encode($data);
        wp_die();
    }
    
    public function doUpdateScan()
    {
        do_action('madeit_security_check_plugin_updates');
        echo json_encode(get_site_transient('madeit_security_update_scan'));
        wp_die();
    }

    public function doBackup()
    {
        do_action('madeit_security_backup');
        echo json_encode(get_site_transient('madeit_security_backup'));
        wp_die();
    }

    public function addHooks()
    {
        add_action('admin_menu', [$this, 'initMenu']);
        add_action('admin_enqueue_scripts', [$this, 'initStyle']);

        add_action('wp_ajax_madeit_security_start_scan', [$this, 'doFileScan']);
        add_action('wp_ajax_madeit_security_stop_scan', [$this, 'stopFileScan']);
        add_action('wp_ajax_madeit_security_update_scan', [$this, 'doUpdateScan']);
        add_action('wp_ajax_madeit_security_backup', [$this, 'doBackup']);
        add_action('wp_ajax_madeit_security_check_scan', [$this, 'checkFileScan']);
    }
}
