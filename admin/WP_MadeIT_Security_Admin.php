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
        $issues = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_issues WHERE issue_fixed IS NULL AND issue_ignored IS NULL');
        if (isset($issues['aantal'])) {
            $count = $issues['aantal'];
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

        add_submenu_page(null, __('Server info', 'wp-security-by-made-it'), __('Server info', 'wp-security-by-made-it'), 'manage_options', 'madeit_security_systeminfo', [$this, 'show_server_info']);
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
        wp_register_style('madeit-table', MADEIT_SECURITY_URL.'/admin/css/table.css', [], null);
        wp_enqueue_style('madeit-table');

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
            if (!is_numeric(sanitize_text_field($_POST['madeit_security_backup_files']))) {
                return __('Backup files value is incorrect', 'wp-security-by-made-it');
            }

            //Check API Key
            $newKey = sanitize_text_field($_POST['madeit_security_maintenance_api_key']);
            $checkApiKey = $this->settings->checkApiKey($newKey);
            if (!isset($checkApiKey['success']) || (isset($checkApiKey['success']) && !$checkApiKey['success'])) {
                update_option('madeit_security_maintenance_api_key', '');
                update_option('madeit_security_maintenance_enable', false);
                $this->defaultSettings = $this->settings->loadDefaultSettings();

                return __('The provided API Key is invalid.', 'wp-security-by-made-it');
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

                $conn_id = @ftp_connect($ftp_server);
                if ($conn_id === false) {
                    return __('Cannot connect to the FTP server.', 'wp-security-by-made-it');
                }
                $login_result = @ftp_login($conn_id, $ftp_username, $ftp_password);
                if ($login_result === false) {
                    return __('FTP credentials are wrong.', 'wp-security-by-made-it');
                }

                if (!empty($destination)) {
                    if (@ftp_nlist($conn_id, $destination) === false) {
                        if (@ftp_mkdir($conn_id, $dir) === false) {
                            return __('Cannot create destination directory on FTP server.', 'wp-security-by-made-it');
                        }
                    }

                    $destination = trailingslashit($destination);
                }

                file_put_contents(WP_CONTENT_DIR.'/file.txt', 'test');
                if (@ftp_put($conn_id, $destination.'file.txt', WP_CONTENT_DIR.'/file.txt', FTP_ASCII)) {
                    unlink(WP_CONTENT_DIR.'/file.txt');
                    if (!@ftp_delete($conn_id, $destination.'file.txt')) {
                        return __('Cannot delete temp file in destination directory on FTP server.', 'wp-security-by-made-it');
                    }
                } else {
                    unlink(WP_CONTENT_DIR.'/file.txt');

                    return __('Cannot create file in destination directory on FTP server.', 'wp-security-by-made-it');
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
                    return __('Cannot connect to S3 bucket', 'wp-security-by-made-it');
                }

                //check if S3 is available
                $s3 = true;
            }

            //Backup settings
            $this->settings->checkTextbox('madeit_security_backup_files');
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
            $wpNotify = new WP_MadeIT_Security_Update($this->settings, $this->db);
            if ($this->defaultSettings['maintenance']['enable'] === true || $this->defaultSettings['scan']['update']) {
                $wpNotify->activateSechduler(false);
            } else {
                $wpNotify->activateSechduler(true);
            }

            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup.php';
            $wpBackup = new WP_MadeIT_Security_Backup($this->settings, $this->db);
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

    public function show_server_info()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_SystemInfo.php';

        $systeminfo = new WP_MadeIT_Security_SystemInfo();
        $systemInfoResult = $systeminfo->getSystemInfo();
        $cronjobs = _get_cron_array();
        $cronJobsInSync = true;
        $fileStats = [];

        //check if cron system is in sync
        foreach ($cronjobs as $time => $crons) {
            if ($time < time()) {
                $cronJobsInSync = false;
            }
        }

        //fetch file stats
        $fileStats[__('# files', 'wp-security-by-made-it')] = [
            'core'   => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE core_file = 1')['aantal'],
            'theme'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE theme_file = 1')['aantal'],
            'plugin' => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE plugin_file = 1')['aantal'],
            'other'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE (core_file = 0 AND theme_file = 0 AND plugin_file = 0)')['aantal'],
        ];

        $fileStats[__('# loaded files', 'wp-security-by-made-it')] = [
            'core'   => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE file_loaded IS NOT NULL AND core_file = 1')['aantal'],
            'theme'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE file_loaded IS NOT NULL AND theme_file = 1')['aantal'],
            'plugin' => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE file_loaded IS NOT NULL AND plugin_file = 1')['aantal'],
            'other'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE file_loaded IS NOT NULL AND (core_file = 0 AND theme_file = 0 AND plugin_file = 0)')['aantal'],
        ];

        $fileStats[__('# checked files', 'wp-security-by-made-it')] = [
            'core'   => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE file_checked IS NOT NULL AND core_file = 1')['aantal'],
            'theme'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE file_checked IS NOT NULL AND theme_file = 1')['aantal'],
            'plugin' => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE file_checked IS NOT NULL AND plugin_file = 1')['aantal'],
            'other'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE file_checked IS NOT NULL AND (core_file = 0 AND theme_file = 0 AND plugin_file = 0)')['aantal'],
        ];

        $fileStats[__('# changed files', 'wp-security-by-made-it')] = [
            'core'   => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE changed = 1 AND core_file = 1')['aantal'],
            'theme'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE changed = 1 AND theme_file = 1')['aantal'],
            'plugin' => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE changed = 1 AND plugin_file = 1')['aantal'],
            'other'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE changed = 1 AND (core_file = 0 AND theme_file = 0 AND plugin_file = 0)')['aantal'],
        ];

        $fileStats[__('# files to backup', 'wp-security-by-made-it')] = [
            'core'   => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE need_backup = 1 AND core_file = 1')['aantal'],
            'theme'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE need_backup = 1 AND theme_file = 1')['aantal'],
            'plugin' => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE need_backup = 1 AND plugin_file = 1')['aantal'],
            'other'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE need_backup = 1 AND (core_file = 0 AND theme_file = 0 AND plugin_file = 0)')['aantal'],
        ];

        $fileStats[__('# files in last backup', 'wp-security-by-made-it')] = [
            'core'   => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE in_backup = 1 AND core_file = 1')['aantal'],
            'theme'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE in_backup = 1 AND theme_file = 1')['aantal'],
            'plugin' => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE in_backup = 1 AND plugin_file = 1')['aantal'],
            'other'  => $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE in_backup = 1 AND (core_file = 0 AND theme_file = 0 AND plugin_file = 0)')['aantal'],
        ];

        include_once MADEIT_SECURITY_ADMIN.'/templates/system_info.php';
    }

    public function show_scan()
    {
        if (isset($_GET['ignore-issue'])) {
            $id = sanitize_text_field($_GET['ignore-issue']);
            $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_issues SET `issue_ignored` = %s WHERE id = %s', time(), $id);
        }
        if (isset($_GET['read-issue'])) {
            $id = sanitize_text_field($_GET['read-issue']);
            $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_issues SET `issue_readed` = %s WHERE id = %s', time(), $id);
        }
        if (isset($_GET['fix-issue'])) {
            $id = sanitize_text_field($_GET['fix-issue']);
            $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_issues SET `issue_fixed` = %s WHERE id = %s', time(), $id);
        }
        
        if (isset($_GET['changes'])) {
            $this->showChanges();
        } elseif (isset($_GET['notexist'])) {
            $this->notExist();
        } else {
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';

            $lastScan = get_site_transient('madeit_security_scan');
            $plugins = $this->db->querySelect('SELECT * FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE plugin_file = 1 AND reason IS NOT NULL AND `ignore` != 1 ORDER BY `plugin_theme` ASC');
            $pluginScanData = [];
            $plugin = '';
            foreach ($plugins as $value) {
                if ($plugin != $value['plugin_theme']) {
                    $plugin = $value['plugin_theme'];
                    $pluginScanData[$plugin] = [];
                }
                if (!isset($pluginScanData[$plugin][$value['reason']])) {
                    $pluginScanData[$plugin][$value['reason']] = [];
                }
                $pluginScanData[$plugin][$value['reason']][] = $value['filename'];
            }

            $themes = $this->db->querySelect('SELECT * FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE theme_file = 1 AND reason IS NOT NULL AND `ignore` != 1 ORDER BY `plugin_theme` ASC');
            $themeScanData = [];
            $theme = '';
            foreach ($themes as $value) {
                if ($theme != $value['plugin_theme']) {
                    $theme = $value['plugin_theme'];
                    $themeScanData[$plugin] = [];
                }
                if (!isset($themeScanData[$plugin][$value['reason']])) {
                    $themeScanData[$plugin][$value['reason']] = [];
                }
                $themeScanData[$plugin][$value['reason']][] = $value['filename'];
            }

            $updateScanData = get_site_transient('madeit_security_update_scan');
            
            $issues = $this->db->querySelect('SELECT * FROM '.$this->db->prefix().'madeit_sec_issues WHERE issue_fixed IS NULL AND issue_ignored IS NULL ORDER BY severity DESC');

            $nonceReplace = wp_create_nonce('madeit_security_replace_file');
            $nonceDelete = wp_create_nonce('madeit_security_delete_file');
            include_once MADEIT_SECURITY_ADMIN.'/templates/scan.php';
        }
    }
    
    private function getSeverityTxt($severity)
    {
        //1 = trivial, 2 => minor 3 => major, 4 => critical, 5 => blocked
        if($severity == 1) {
            return __('Trivial', 'wp-security-by-made-it');
        }
        elseif($severity == 2) {
            return __('Minor', 'wp-security-by-made-it');
        }
        elseif($severity == 3) {
            return __('Major', 'wp-security-by-made-it');
        }
        elseif($severity == 4) {
            return __('Critical', 'wp-security-by-made-it');
        }
        elseif($severity == 5) {
            return __('Blocked', 'wp-security-by-made-it');
        }
        return "";
    }
    
    private function getPluginInfoByFile($filenameMd5)
    {
        $fileData = $this->db->querySingleRecord('SELECT * FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE plugin_file = 1 AND filename_md5 = %s', $filenameMd5);
        if($fileData == null) {
            return null;
        }
        
        $plugin = $fileData['plugin_theme'];
        
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        $wp_plugin = new WP_MadeIT_Security_Plugin();
        $pluginsData = $wp_plugin->getPlugins();
        $version = '';
        $path = WP_PLUGIN_DIR;
        $data = null;
        foreach ($pluginsData as $key => $pluginData) {
            if ($pluginData['slug'] == $plugin) {
                $version = $pluginData['version'];
                $path .= '/'.substr($key, 0, strpos($key, '/'));
                $data = $pluginData;
            }
        }
        
        return [
            'plugin_data' => $data,
            'version' => $version,
            'plugin' => $plugin,
        ];
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
            $fileData = $this->db->querySingleRecord('SELECT * FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE filename_md5 = %s', $file);

            $localFile = ABSPATH.$fileData['filename'];

            $pluginName = $fileData['plugin_theme'];
            $startDir = WP_PLUGIN_DIR;
            if (strpos($pluginName, '/') > 0) {
                $pluginDir = str_replace(ABSPATH, '', $startDir.'/'.substr($pluginName, 0, strpos($pluginName, '/'))).'/';
            } else {
                $pluginDir = str_replace(ABSPATH, '', $startDir.'/'.$pluginName).'/';
            }
            $fileName = preg_replace('/'.preg_quote($pluginDir, '/').'/', '', $fileData['filename'], 1);

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
                        $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_issues SET `issue_fixed` = %s WHERE filename_md5 = %s AND issue_fixed IS NULL', time(), $file);
                        $this->disIgnoreFile($plugin, $file);
                        $this->replace($plugin, $fileName, $localFile, $version);
                        $list = true;
                        $fileReplacedSuccesfull = $file;
                    }
                } elseif (isset($_GET['delete'])) {
                    //Replace the current file with the original
                    $nonce = sanitize_text_field($_GET['replace']);
                    if (!wp_verify_nonce($nonce, 'madeit_security_delete_file')) {
                        // This nonce is not valid.
                        wp_die('Security check');
                    } else {
                        $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_issues SET `issue_fixed` = %s WHERE filename_md5 = %s AND issue_fixed IS NULL', time(), $file);
                        $this->disIgnoreFile($plugin, $file);
                        $this->delete($plugin, $localFile);
                        $list = true;
                        $fileDeletedSuccesfull = $file;
                    }
                } else {
                    if (false) { //Use Made I.T. Cache to not overlode WP repo. TODO: make setting for this.
                        $remoteUrl = 'https://madeit.be/wordpress-onderhoud/api/1.0/wp/plugin/'.$plugin.'/getFile?version='.$version.'&file='.$fileName;
                    } else {
                        $remoteUrl = 'https://plugins.trac.wordpress.org/browser/'.$plugin.'/tags/'.$version.'/'.$fileName.'?format=txt';
                    }
                    $a = explode("\n", file_get_contents($localFile));
                    $b = explode("\n", file_get_contents($remoteUrl));
                    if (!class_exists('DiffFiles')) {
                        require_once MADEIT_SECURITY_DIR.'/inc/compare/Diff.php';
                    }
                    $diff = new DiffFiles($a, $b, []);

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
        if($list) {
            $issues = $this->db->querySelect('SELECT * FROM '.$this->db->prefix().'madeit_sec_issues WHERE issue_fixed IS NULL AND issue_ignored IS NULL ORDER BY severity DESC');
            $nonceReplace = wp_create_nonce('madeit_security_replace_file');
            
            include_once MADEIT_SECURITY_ADMIN.'/templates/list-issues.php';
        }
        if ($list && false) {
            $nonce = wp_create_nonce('madeit_security_ignore_file');
            $nonceDelete = wp_create_nonce('madeit_security_delete_file');

            $files = $this->db->querySelect('SELECT * FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE plugin_file = 1 AND reason IS NOT NULL AND `ignore` != 1 AND plugin_theme = %s', $plugin);
            include_once MADEIT_SECURITY_ADMIN.'/templates/list-changed-files.php';
        }
    }

    /* 
     * @deprecated
    */
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
            $fileData = $this->db->querySingleRecord('SELECT * FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE filename_md5 = %s', $file);

            $localFile = ABSPATH.$fileData['filename'];

            $pluginName = $fileData['plugin_theme'];
            $startDir = WP_PLUGIN_DIR;
            $pluginDir = str_replace(ABSPATH, '', $path.'/');
            $fileName = preg_replace('/'.preg_quote($pluginDir, '/').'/', '', $fileData['filename'], 1);

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
                } elseif (isset($_GET['delete'])) {
                    //Delete this file
                    $nonce = sanitize_text_field($_GET['delete']);
                    if (!wp_verify_nonce($nonce, 'madeit_security_delete_file')) {
                        // This nonce is not valid.
                        wp_die('Security check');
                    } else {
                        $this->disIgnoreFile($plugin, $file);
                        $this->delete($plugin, $localFile);
                        $list = true;
                        $fileDeletedSuccesfull = $file;
                    }
                } else {
                    $a = explode("\n", file_get_contents($localFile));
                    $b = [];
                    if (!class_exists('DiffFiles')) {
                        require_once MADEIT_SECURITY_DIR.'/inc/compare/Diff.php';
                    }
                    $diff = new DiffFiles($a, $b, []);

                    if (!class_exists('Diff_Renderer_Html_SideBySide')) {
                        require_once MADEIT_SECURITY_DIR.'/inc/compare/Diff/Renderer/Html/SideBySide.php';
                    }
                    $renderer = new Diff_Renderer_Html_SideBySide();
                }
            }
            if (!$list) {
                $nonce = wp_create_nonce('madeit_security_ignore_file');
                $nonceDelete = wp_create_nonce('madeit_security_delete_file');
                include_once MADEIT_SECURITY_ADMIN.'/templates/notexisting_files.php';
            }
        }
        if ($list) {
            $nonce = wp_create_nonce('madeit_security_ignore_file');

            $files = $this->db->querySelect('SELECT * FROM `'.$this->db->prefix()."madeit_sec_filelist` WHERE plugin_file = 1 AND reason = 'File not exist in repo' AND `ignore` != 1 AND plugin_theme = %s", $plugin);
            include_once MADEIT_SECURITY_ADMIN.'/templates/list-not-exist-files.php';
        }
    }

    private function timeAgo($ptime)
    {
        $etime = time() - $ptime;

        if ($etime < 1) {
            return __('less than 1 second', 'wp-security-by-made-it');
        }

        $a = [12 * 30 * 24 * 60 * 60 => 'year',
                   30 * 24 * 60 * 60 => 'month',
                   24 * 60 * 60      => 'day',
                   60 * 60           => 'hour',
                   60                => 'minute',
                   1                 => 'second',
        ];

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);

                if ($str == 'year') {
                    return sprintf(_n('%s year', '%s years', $r, 'wp-security-by-made-it'), $r);
                }
                if ($str == 'month') {
                    return sprintf(_n('%s month', '%s months', $r, 'wp-security-by-made-it'), $r);
                }
                if ($str == 'day') {
                    return sprintf(_n('%s day', '%s days', $r, 'wp-security-by-made-it'), $r);
                }
                if ($str == 'hour') {
                    return sprintf(_n('%s hour', '%s hours', $r, 'wp-security-by-made-it'), $r);
                }
                if ($str == 'minute') {
                    return sprintf(_n('%s minute', '%s minutes', $r, 'wp-security-by-made-it'), $r);
                }
                if ($str == 'second') {
                    return sprintf(_n('%s second', '%s seconds', $r, 'wp-security-by-made-it'), $r);
                }

                return $r;
            }
        }
    }

    private function ignoreFile($plugin, $file)
    {
        $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET `ignore` = 1 WHERE reason IS NOT NULL AND filename_md5 = %s AND plugin_theme = %s', $file, $plugin);
    }

    private function ignoreAll($plugin)
    {
        $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET `ignore` = 1 WHERE reason IS NOT NULL AND plugin_theme = %s', $plugin);
    }

    private function disIgnoreFile($plugin, $file)
    {
        $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET `ignore` = 0 WHERE reason IS NOT NULL AND filename_md5 = %s AND plugin_theme = %s', $file, $plugin);
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

    private function delete($plugin, $localFile)
    {
        unlink($localFile);
    }

    public function doFileScan()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_LoadFiles.php';
        $scan = new WP_MadeIT_Security_LoadFiles($this->settings, $this->db);
        $scan->startLoadingFiles();
        echo json_encode(['success' => true]);
        wp_die();
    }

    public function stopFileScan()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_LoadFiles.php';
        $scan = new WP_MadeIT_Security_LoadFiles($this->settings, $this->db);
        $scan->stopLoadingFiles();
        echo json_encode(['success' => true]);
        wp_die();
    }

    public function checkFileScan()
    {
        $result = get_site_transient('madeit_security_scan');

        if ($result === false) {
            $data = ['success' => true, 'completed' => false, 'running' => false];
        } else {
            $lastTimeAgo = '';
            if (isset($result['last_com_time'])) {
                $lastTimeAgo = sprintf(esc_html(__('Last result %s ago.', 'wp-security-by-made-it')), $this->timeAgo($result['last_com_time']));
            }
            $data = [
                'success'       => true,
                'completed'     => $result['done'],
                'running'       => !$result['done'] && !$result['stop'],
                'result'        => $result,
                'time_ago'      => sprintf(esc_html(__('Last scan %s ago.', 'wp-security-by-made-it')), $this->timeAgo($result['start_time'])),
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
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup.php';
        $wp_madeit_security_backup = new WP_MadeIT_Security_Backup($this->settings, $this->db);
        $wp_madeit_security_backup->addHooks();

        do_action('madeit_security_backup');
        echo json_encode(get_site_transient('madeit_security_backup'));
        wp_die();
    }

    public function doUpdate()
    {
        //Update plugins
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin_Installer.php';
        $cPlugin = new WP_MadeIT_Security_Plugin();
        $cPluginInstaller = new WP_MadeIT_Security_Plugin_Installer();

        $plugins = $cPlugin->getPlugins();
        $pluginsUpdated = [];
        $pluginErrors = [];
        ob_start();
        foreach ($plugins as $plugin => $values) {
            if ($values['repository'] == 'WORDPRESS.ORG' && version_compare($values['version'], $values['latest_version'], '<')) {
                //update plugin
                $downloadUrl = $values['download_url'];
                $result = $cPluginInstaller->upgradeWithPackage($plugin, $downloadUrl);
                if ($result === true) {
                    $pluginsUpdated[] = $values['name'];
                } else {
                    $pluginErrors[$values['name']] = $result;
                }
            }
        }
        $out = ob_get_clean();

        //Update themes
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme_Installer.php';
        $cTheme = new WP_MadeIT_Security_Theme();
        $cThemeInstaller = new WP_MadeIT_Security_Theme_Installer();

        $themes = $cTheme->getThemes();
        $themesUpdated = [];
        $themesErrors = [];
        ob_start();
        foreach ($themes as $theme => $values) {
            if ($values['repository'] == 'WORDPRESS.ORG' && version_compare($values['version'], $values['latest_version'], '<')) {
                //update plugin
                $downloadUrl = $values['download_url'];
                $result = $cThemeInstaller->upgradeWithPackage($theme, $downloadUrl);
                if ($result === true) {
                    $themesUpdated[] = $values['name'];
                } else {
                    $themesErrors[$values['name']] = $result;
                }
            }
        }
        $out = ob_get_clean();

        //update core
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core_Installer.php';
        $cCore = new WP_MadeIT_Security_Core();
        $cCoreInstaller = new WP_MadeIT_Security_Core_Installer();

        $coreUpdated = [];
        $coreErrors = [];
        ob_start();
        if (version_compare($cCore->getCurrentWPVersion(), $cCore->getLatestWPVersion(), '<')) {
            //update plugin
            $downloadUrl = $cCore->getNewPacakgeUrl();
            if ($downloadUrl != null) {
                $result = $cCoreInstaller->upgradeWithPackage($downloadUrl, false);
            } else {
                $result = 'No download available';
            }
            if ($result === true) {
                $coreUpdated[] = 'CORE';
            } else {
                $themesErrors[] = $result;
            }
        }
        $out = ob_get_clean();

        do_action('madeit_security_check_plugin_updates');

        echo json_encode([
            'success'         => count($pluginErrors) == 0 && count($themesErrors) == 0 && count($coreErrors) == 0,
            'updated_plugins' => $pluginsUpdated,
            'errored_plugins' => $pluginErrors,
            'updated_themes'  => $themesUpdated,
            'errored_themes'  => $themesErrors,
            'updated_core'    => $coreUpdated,
            'errored_core'    => $coreErrors,
            'scan'            => get_site_transient('madeit_security_update_scan'),
        ]);
        wp_die();
    }

    public function checkBackup()
    {
        $result = get_site_transient('madeit_security_backup');

        if ($result === false) {
            $data = ['success' => true, 'completed' => false, 'running' => false];
        } else {
            $lastTimeAgo = '';
            if (isset($result['last_com_time'])) {
                $lastTimeAgo = sprintf(esc_html(__('Last change %s ago.', 'wp-security-by-made-it')), $this->timeAgo($result['last_com_time']));
            }
            $data = [
                'success'       => true,
                'completed'     => $result['done'],
                'running'       => !$result['done'] && !$result['stop'],
                'time_ago'      => sprintf(esc_html(__('Last backup %s ago.', 'wp-security-by-made-it')), $this->timeAgo($result['time'])),
                'last_time_ago' => $lastTimeAgo,
                'result'        => $result,
            ];
        }

        echo json_encode($data);
        wp_die();
    }

    public function stopBackup()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup.php';
        $wp_madeit_security_backup = new WP_MadeIT_Security_Backup($this->settings, $this->db);
        $wp_madeit_security_backup->stopBackup();
        echo json_encode(['success' => true]);
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
        add_action('wp_ajax_madeit_security_backup_check', [$this, 'checkBackup']);
        add_action('wp_ajax_madeit_security_backup_stop', [$this, 'stopBackup']);
        add_action('wp_ajax_madeit_security_check_scan', [$this, 'checkFileScan']);
        add_action('wp_ajax_madeit_security_do_update', [$this, 'doUpdate']);
    }
}
