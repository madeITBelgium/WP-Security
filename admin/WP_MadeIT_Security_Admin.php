<?php

class WP_MadeIT_Security_Admin
{
    private $defaultSettings = [];
    private $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
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
        if ($this->defaultSettings['scan']['repo']['core'] || $this->defaultSettings['scan']['repo']['plugin'] || $this->defaultSettings['scan']['repo']['theme']) {
            $repoScanData = get_site_transient('madeit_security_repo_scan');
            $count += count($repoScanData['core']['plugins']);
            if (isset($repoScanData['plugin']['count_plugin_errors'])) {
                $count += $repoScanData['plugin']['count_plugin_errors'];
            } else {
                $count += count($repoScanData['plugin']['plugins']);
            }
            $count += count($repoScanData['theme']['themes']);
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
        add_menu_page(__('Security & Maintenance', 'madeit_security'), __('Security', 'madeit_security').' '.$new, 'manage_options', 'madeit_security', [$this, 'show_dashboard'], MADEIT_SECURITY_URL.'assets/icon-16x16.png', 9999);
        add_submenu_page('madeit_security', __('Security Dashboard', 'madeit_security'), __('Dashboard', 'madeit_security'), 'manage_options', 'madeit_security', [$this, 'show_dashboard']);
        add_submenu_page('madeit_security', __('Security Scan', 'madeit_security'), __('Scan', 'madeit_security'), 'manage_options', 'madeit_security_scan', [$this, 'show_scan']);
        add_submenu_page('madeit_security', __('Security Settings', 'madeit_security'), __('Settings', 'madeit_security'), 'manage_options', 'madeit_security_settings', [$this, 'settings']);
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
            $this->settings->checkCheckbox('madeit_security_scan_repo_fast');
            $this->settings->checkCheckbox('madeit_security_scan_repo_core');
            $this->settings->checkCheckbox('madeit_security_scan_repo_theme');
            $this->settings->checkCheckbox('madeit_security_scan_repo_plugin');
            $this->settings->checkTextbox('madeit_security_maintenance_api_key');
            $this->settings->checkTextbox('madeit_security_api_key');
            $this->settings->checkCheckbox('madeit_security_maintenance_enable');
            $this->settings->checkCheckbox('madeit_security_maintenance_backup');
            $this->settings->checkCheckbox('madeit_security_scan_update');

            $checkApiKey = $this->settings->checkApiKey(get_option('madeit_security_maintenance_api_key', 'NONE'));
            if (!isset($checkApiKey['success']) || (isset($checkApiKey['success']) && !$checkApiKey['success'])) {
                update_option('madeit_security_maintenance_api_key', '');
                update_option('madeit_security_maintenance_enable', false);
                $this->defaultSettings = $this->settings->loadDefaultSettings();

                return 'The provided API Key is invalid.';
            } elseif (isset($checkApiKey['success']) && $checkApiKey['success']) {
                $this->defaultSettings = $this->settings->loadDefaultSettings();
                update_option('madeit_security_api_key', $this->defaultSettings['maintenance']['key']);
                update_option('madeit_security_maintenance_enable', true);

                require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Maintenance.php';
                $wp_maintenance = new WP_MadeIT_Security_Maintenance($this->settings);
                $wp_maintenance->setUp();
            }
            $this->defaultSettings = $this->settings->loadDefaultSettings();

            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Update.php';
            $wpNotify = new WP_MadeIT_Security_Update($this->settings);
            if ($this->defaultSettings['maintenance']['enable'] === true || $this->defaultSettings['scan']['update']) {
                $wpNotify->activateSechduler(false);
            } else {
                $wpNotify->activateSechduler(true);
            }

            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup.php';
            $wpBackup = new WP_MadeIT_Security_Backup($this->settings);
            if ($this->defaultSettings['maintenance']['backup'] === true) {
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
        }
        elseif (isset($_GET['notexist'])) {
            //TODO
        } else {
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';

            $repoScanData = get_site_transient('madeit_security_repo_scan');
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
                $repoScanData = get_site_transient('madeit_security_repo_scan');
                if (isset($repoScanData['plugin']['plugins'][$plugin])) {
                    $files = array_keys($repoScanData['plugin']['plugins'][$plugin]);

                    foreach ($files as $file) {
                        $files[$file] = true;
                    }
                    $this->ignoreAll($plugin, $files);
                }
            }
        }
        if (isset($_GET['file']) && strlen($version) > 2) {
            $file = sanitize_text_field($_GET['file']);
            $localFile = $path.'/'.$file;
            $error = null;
            $list = false;
            if (!is_file($localFile) || strpos($file, '../') === true) {
                $error = sprintf(__('Local file %s doesn\'t exist on your WordPress installation.', 'madeit_security'), $file);
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
                } 
                elseif (isset($_GET['replace'])) {
                    //Replace the current file with the original
                    $nonce = sanitize_text_field($_GET['replace']);
                    if (!wp_verify_nonce($nonce, 'madeit_security_replace_file')) {
                        // This nonce is not valid.
                        wp_die('Security check');
                    } else {
                        $this->disIgnoreFile($plugin, $file);
                        $this->replace($plugin, $file, $localFile, $version);
                        $list = true;
                        $fileReplacedSuccesfull = $file;
                    }
                } else {
                    if (true) { //Use Made I.T. Cache to not overlode WP repo. TODO: make setting for this.
                        $remoteUrl = 'https://madeit.be/wordpress-onderhoud/api/1.0/wp/plugin/'.$plugin.'/getFile?version='.$version.'&file='.$file;
                    } else {
                        $remoteUrl = 'https://plugins.trac.wordpress.org/browser/'.$plugin.'/tags/'.$version.'/'.$file.'?format=txt';
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
            $repoScanData = get_site_transient('madeit_security_repo_scan');
            if (isset($repoScanData['plugin']['plugins'][$plugin])) {
                $files = array_keys($repoScanData['plugin']['plugins'][$plugin]);
                $nonce = wp_create_nonce('madeit_security_ignore_file');

                include_once MADEIT_SECURITY_ADMIN.'/templates/list-changed-files.php';
            }
        }
    }

    private function timeAgo($ptime)
    {
        $etime = time() - $ptime;

        if ($etime < 1) {
            return __('less than 1 second', 'madeit_security');
        }

        $a = [12 * 30 * 24 * 60 * 60 => __('year', 'madeit_security'),
                   30 * 24 * 60 * 60 => __('month', 'madeit_security'),
                   24 * 60 * 60      => __('day', 'madeit_security'),
                   60 * 60           => __('hour', 'madeit_security'),
                   60                => __('minute', 'madeit_security'),
                   1                 => __('second', 'madeit_security'),
        ];

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);

                return $r.' '.$str.($r > 1 ? 's' : '');
            }
        }
    }

    private function isFileIgnored($plugin, $file)
    {
        $ignoreData = get_site_transient('madeit_security_ignore_scan');

        return isset($ignoreData[$plugin][$file]);
    }

    private function ignoreFile($plugin, $file)
    {
        $ignoreData = get_site_transient('madeit_security_ignore_scan');
        if (!is_array($ignoreData)) {
            $ignoreData = [];
        }

        if (!isset($ignoreData[$plugin])) {
            $ignoreData[$plugin] = [];
        }

        if (!isset($ignoreData[$plugin][$file])) {
            $ignoreData[$plugin][$file] = true;
        }
        set_site_transient('madeit_security_ignore_scan', $ignoreData);
    }

    private function ignoreAll($plugin, $files)
    {
        $ignoreData = get_site_transient('madeit_security_ignore_scan');
        if (!is_array($ignoreData)) {
            $ignoreData = [];
        }

        if (!isset($ignoreData[$plugin])) {
            $ignoreData[$plugin] = [];
        }

        $ignoreData[$plugin] = $files;
        set_site_transient('madeit_security_ignore_scan', $ignoreData);
    }

    private function disIgnoreFile($plugin, $file)
    {
        $ignoreData = get_site_transient('madeit_security_ignore_scan');
        if (!is_array($ignoreData)) {
            $ignoreData = [];
        }

        if (isset($ignoreData[$plugin][$file])) {
            unset($ignoreData[$plugin][$file]);
        }
        set_site_transient('madeit_security_ignore_scan', $ignoreData);
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
        
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Scan.php';
        $scan = new WP_MadeIT_Security_Scan();
        $scan->fullScanAgainstRepoFiles();
    }

    public function doRepoScan()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Scan.php';
        $scan = new WP_MadeIT_Security_Scan();
        echo json_encode($scan->fullScanAgainstRepoFiles());
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

        add_action('wp_ajax_madeit_security_repo_scan', [$this, 'doRepoScan']);
        add_action('wp_ajax_madeit_security_update_scan', [$this, 'doUpdateScan']);
        add_action('wp_ajax_madeit_security_backup', [$this, 'doBackup']);
        //add_action('init', array($this, 'init'));
    }
}
