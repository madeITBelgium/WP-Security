<?php

class WP_MadeIT_Security_LoadFiles
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function activateSechduler($deactivate)
    {
        if ($deactivate) {
            wp_clear_scheduled_hook('madeit_security_loadfiles');
        } else {
            if (!wp_next_scheduled('madeit_security_loadfiles')) {
                wp_schedule_event(time(), 'daily', 'madeit_security_loadfiles');
            }
        }
    }

    public function startLoadingFiles()
    {
        $result = get_site_transient('madeit_security_scan');

        $emptyResult = [
            'core'    => ['completed' => false, 'loading' => false, 'files_checked' => 0, 'success' => false],
            'plugin'  => ['completed' => false, 'loading' => false, 'files_checked' => 0, 'plugin_checked' => 0, 'success' => false],
            'theme'   => ['completed' => false, 'loading' => false, 'files_checked' => 0, 'themes_checked' => 0, 'success' => false],
            'content' => ['completed' => false, 'loading' => false, 'files_checked' => 0, 'success' => false],
            'other'   => ['completed' => false, 'loading' => false, 'files_checked' => 0, 'success' => false],
        ];

        if ($result === false) {
            $result = [
                'start_time'    => time(),
                'last_com_time' => null,
                'step'          => 0,
                'done'          => true,
                'stop'          => false,
                'result'        => $emptyResult,
            ];
            set_site_transient('madeit_security_scan', $result);
        }

        if ($result['done'] == false && $result['start_time'] <= time() - 60 * 30) {
            //Stop existing running job
            $result['stop'] = true;
            set_site_transient('madeit_security_scan', $result);
        } else {
            $result = [];
            $result['start_time'] = time();
            $result['step'] = 0;
            $result['done'] = false;
            $result['stop'] = false;
            $result['result'] = $emptyResult;
            set_site_transient('madeit_security_scan', $result);

            //start job
            wp_schedule_single_event(time(), 'madeit_security_loadfiles');
        }
    }

    public function stopLoadingFiles()
    {
        $result = get_site_transient('madeit_security_scan');
        $result['stop'] = true;
        set_site_transient('madeit_security_scan', $result);
    }

    public function getResultLoadingFiles($output = 'array')
    {
        $result = get_site_transient('madeit_security_scan');
        if ($output == 'array') {
            return $result;
        } elseif ($output == 'json') {
        }
    }

    public function loadfiles()
    {
        $bigRun = false;
        $scanForBackup = false;

        $result = get_site_transient('madeit_security_scan');

        if ($result['done'] == false && $result['stop'] == false) {
            if ($result['stop'] == true) {
                return;
            }

            $run = false;
            if (($bigRun || !$run) && $result['step'] == 0) {
                //Set database
                $run = true;

                //Update db md5 codes
                $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET old_md5 = new_md5 WHERE old_md5 <> new_md5');
                $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET file_loaded = null');
                $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET file_checked = null, reason = null WHERE is_safe = 0 OR `reason` IS NOT NULL');

                $result['step'] = 1;
                $result['last_com_time'] = time();
                set_site_transient('madeit_security_scan', $result);

                if ($this->checkToStop()) {
                    return;
                }
                if (!$bigRun) {
                    //start next job
                    $this->startNextJob();

                    return;
                }
            }

            if (($bigRun || !$run) && $result['step'] == 1) {
                //Load core files
                $run = true;

                $this->loadCore();

                $result['step'] = 2;
                $result['result']['core']['loading'] = true;
                $result['last_com_time'] = time();
                set_site_transient('madeit_security_scan', $result);

                if ($this->checkToStop()) {
                    return;
                }
                if (!$bigRun) {
                    //start next job
                    $this->startNextJob();

                    return;
                }
            }

            if (($bigRun || !$run) && $result['step'] == 2) {
                //Do plugin scan
                $this->loadPlugin();

                $result['step'] = 3;
                $result['result']['plugin']['loading'] = true;
                $result['last_com_time'] = time();
                set_site_transient('madeit_security_scan', $result);

                if ($this->checkToStop()) {
                    return;
                }
                if (!$bigRun) {
                    //start next job
                    $this->startNextJob();

                    return;
                }
            }

            if (($bigRun || !$run) && $result['step'] == 3) {
                //Do theme scan
                $this->loadTheme();

                $result['step'] = 4;
                $result['result']['theme']['loading'] = true;
                $result['last_com_time'] = time();
                set_site_transient('madeit_security_scan', $result);

                if ($this->checkToStop()) {
                    return;
                }
                if (!$bigRun) {
                    //start next job
                    $this->startNextJob();

                    return;
                }
            }

            if (($bigRun || !$run) && $result['step'] == 4) {
                //Scan completed

                //Change changed files
                if (!$scanForBackup) {
                    $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET file_changed = %s, file_checked = null, changed = 1 WHERE old_md5 <> new_md5 AND old_md5 IS NOT NULL', time());

                    //Delete removed files
                    $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET file_deleted = %s WHERE file_loaded IS NULL', time());
                }

                //Backup
                $result['step'] = 10;

                //Scan
                $result['step'] = 5;
                $result['last_com_time'] = time();

                set_site_transient('madeit_security_scan', $result);

                if ($this->checkToStop()) {
                    return;
                }
                if (!$bigRun) {
                    //start next job
                    $this->startNextJob();

                    return;
                }
            }

            if (($bigRun || !$run) && $result['step'] == 5) {
                //Scan core
                $count = 1;
                while ($count > 0 && $count != null) {
                    require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core_Scan.php';
                    $core = new WP_MadeIT_Security_Core_Scan($this->db);
                    $coreResult = $core->scan();

                    $count = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_filelist WHERE core_file = 1 AND (file_checked = 0 OR file_checked IS NULL)');
                    if ($this->checkToStop()) {
                        return;
                    }
                    if ($count != null && $count['aantal'] > 0 && !$bigRun) {
                        $this->startNextJob();

                        return;
                    }
                    $count = isset($count['aantal']) ? $count['aantal'] : 0;
                }
                $result['step'] = 6;
                $result['result']['core']['completed'] = true;
                $result['last_com_time'] = time();

                $errorFiles = 0;
                $count = $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE core_file = 1 AND reason IS NOT NULL AND `ignore` != 1 ORDER BY `file_changed` DESC');
                if ($count != null && $count['aantal'] > 0) {
                    $errorFiles = $count['aantal'];
                }

                $result['result']['core']['success'] = $errorFiles == 0;
                set_site_transient('madeit_security_scan', $result);

                if ($this->checkToStop()) {
                    return;
                }
                if (!$bigRun) {
                    //start next job
                    $this->startNextJob();

                    return;
                }
            }

            if (($bigRun || !$run) && $result['step'] == 6) {
                //plugin core
                $count = 1;
                while ($count > 0 && $count != null) {
                    require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin_Scan.php';
                    $core = new WP_MadeIT_Security_Plugin_Scan($this->db);
                    $coreResult = $core->scan();

                    $count = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_filelist WHERE plugin_file = 1 AND (file_checked = 0 OR file_checked IS NULL)');
                    if ($this->checkToStop()) {
                        return;
                    }
                    if ($count != null && $count['aantal'] > 0 && !$bigRun) {
                        $this->startNextJob();

                        return;
                    }
                    $count = isset($count['aantal']) ? $count['aantal'] : 0;
                }

                $resultAgain = get_site_transient('madeit_security_scan_again');
                $plugins = new WP_MadeIT_Security_Plugin();
                $plugins = $plugins->getPlugins(false);
                $i = 0;
                foreach ($plugins as $plugin => $value) {
                    if (isset($resultAgain[$value['slug']])) {
                        foreach ($resultAgain[$value['slug']] as $file => $hash) {
                            $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist set file_checked = NULL WHERE filename_md5 = %s', $hash);
                            unset($resultAgain[$value['slug']][$file]);
                            $i++;

                            set_site_transient('madeit_security_scan_again', $resultAgain);

                            if ($i % 1000 == 0 && !$bigRun) {
                                $this->startNextJob();

                                return;
                            }
                        }
                    }
                }

                $errorFiles = 0;
                $count = $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE plugin_file = 1 AND reason IS NOT NULL AND `ignore` != 1');
                if ($count != null && $count['aantal'] > 0) {
                    $errorFiles = $count['aantal'];
                }

                $result['step'] = 7;
                $result['result']['plugin']['completed'] = true;
                $result['result']['plugin']['success'] = $errorFiles == 0;
                $result['last_com_time'] = time();
                set_site_transient('madeit_security_scan', $result);

                if ($this->checkToStop()) {
                    return;
                }
                if (!$bigRun) {
                    //start next job
                    $this->startNextJob();

                    return;
                }
            }

            if (($bigRun || !$run) && $result['step'] == 7) {
                //theme core
                $count = 1;
                while ($count > 0 && $count != null) {
                    require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme_Scan.php';
                    $core = new WP_MadeIT_Security_Theme_Scan($this->db);
                    $coreResult = $core->scan();

                    $count = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_filelist WHERE theme_file = 1 AND (file_checked = 0 OR file_checked IS NULL)');
                    if ($this->checkToStop()) {
                        return;
                    }
                    if ($count != null && $count['aantal'] > 0 && !$bigRun) {
                        $this->startNextJob();

                        return;
                    }
                    $count = isset($count['aantal']) ? $count['aantal'] : 0;
                }
                $resultAgain = get_site_transient('madeit_security_scan_again');
                if (!class_exists('WP_MadeIT_Security_Theme')) {
                    include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';
                }
                $themes = new WP_MadeIT_Security_Theme();
                $themes = $themes->getThemes(false);
                $i = 0;
                foreach ($themes as $theme => $value) {
                    if (isset($resultAgain[$value['theme']])) {
                        foreach ($resultAgain[$value['theme']] as $file => $hash) {
                            $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist set file_checked = NULL WHERE filename_md5 = %s', $hash);
                            unset($resultAgain[$value['theme']][$file]);
                            $i++;

                            set_site_transient('madeit_security_scan_again', $resultAgain);

                            if ($i % 1000 == 0 && !$bigRun) {
                                $this->startNextJob();

                                return;
                            }
                        }
                    }
                }

                $errorFiles = 0;
                $count = $this->db->querySingleRecord('SELECT count(*) as aantal FROM `'.$this->db->prefix().'madeit_sec_filelist` WHERE theme_file = 1 AND `reason` IS NOT NULL AND `ignore` != 1');
                if ($count != null && $count['aantal'] > 0) {
                    $errorFiles = $count['aantal'];
                }
                $result['step'] = 8;
                $result['result']['theme']['completed'] = true;
                $result['result']['theme']['success'] = $errorFiles == 0;
                $result['last_com_time'] = time();
                set_site_transient('madeit_security_scan', $result);

                if ($this->checkToStop()) {
                    return;
                }
                if (!$bigRun) {
                    //start next job
                    $this->startNextJob();

                    return;
                }
            }

            if (($bigRun || !$run) && $result['step'] == 8) {
                //Scan other

                $result['step'] = 9;
                $result['result']['content']['completed'] = true;
                $result['done'] = true;
                $result['last_com_time'] = time();
                set_site_transient('madeit_security_scan', $result);
            }
        }
    }

    public function checkToStop()
    {
        $result = get_site_transient('madeit_security_scan');
        if ($result['stop'] == true) {
            return true;
        }

        return false;
    }

    public function startNextJob()
    {
        wp_schedule_single_event(time(), 'madeit_security_loadfiles');
    }

    private function fileLoadDirectory($directory, $type, $pluginTheme = null)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (is_dir($directory.'/'.$file)) {
                    $this->fileLoadDirectory($directory.'/'.$file, $type);
                } else {
                    $this->updateFileToDB($directory.'/'.$file, md5_file($directory.'/'.$file), $type, $pluginTheme);
                }
            }
        }

        $dir->close();
    }

    private function loadCore()
    {
        $directory = ABSPATH;
        $files = [];
        $dir = dir($directory);

        $wpHeadFiles = [
            'wp-admin',
            'wp-content',
            'wp-includes',
            'index.php',
            'license.txt',
            'readme.html',
            'wp-activate.php',
            'wp-blog-header.php',
            'wp-comments-post.php',
            'wp-config-sample.php',
            'wp-cron.php',
            'wp-links-opml.php',
            'wp-load.php',
            'wp-login.php',
            'wp-mail.php',
            'wp-settings.php',
            'wp-signup.php',
            'wp-trackback.php',
            'xmlrpc.php',
        ];

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (in_array($file, $wpHeadFiles)) {
                    if ($directory.'/'.$file == WP_CONTENT_DIR) {
                        $this->fileLoadDirectory($directory.'/'.$file, 'WP_CONTENT');
                    } elseif (is_dir($directory.'/'.$file)) {
                        $this->fileLoadDirectory($directory.'/'.$file, 'CORE');
                    } else {
                        $this->updateFileToDB($directory.'/'.$file, md5_file($directory.'/'.$file), 'CORE');
                    }
                } elseif (false && $scanOutsideWP) {
                    if (is_dir($directory.'/'.$file)) {
                        $this->fileLoadDirectory($directory.'/'.$file, 'OTHER');
                    } else {
                        $this->updateFileToDB($directory.'/'.$file, md5_file($directory.'/'.$file), 'OTHER');
                    }
                }
            }
        }

        $dir->close();
    }

    private function loadPlugin()
    {
        if (!class_exists('WP_MadeIT_Security_Plugin')) {
            include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        }
        $plugins = new WP_MadeIT_Security_Plugin();
        $plugins = $plugins->getPlugins(false);

        foreach ($plugins as $plugin => $value) {
            $startDir = WP_PLUGIN_DIR;
            if (strpos($plugin, '/') > 0) {
                $pluginDir = $startDir.'/'.substr($plugin, 0, strpos($plugin, '/'));
            } else {
                $pluginDir = $startDir.'/'.$plugin;
            }
            $this->fileLoadDirectory($pluginDir, 'PLUGIN', substr($plugin, 0, strpos($plugin, '/')));

            if ($this->checkToStop()) {
                return;
            }
        }
    }

    private function loadTheme()
    {
        if (!class_exists('WP_MadeIT_Security_Theme')) {
            include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';
        }
        $themes = new WP_MadeIT_Security_Theme();
        $themes = $themes->getThemes(false);
        $hashes = [];

        foreach ($themes as $key => $value) {
            $theme = $value['theme'];

            $startDir = WP_CONTENT_DIR.'/themes';
            if (strpos($theme, '/') > 0) {
                $themeDir = $startDir.'/'.substr($theme, 0, strpos($theme, '/'));
            } else {
                $themeDir = $startDir.'/'.$theme;
            }

            $this->fileLoadDirectory($themeDir, 'THEME', $theme);

            if ($this->checkToStop()) {
                return;
            }
        }
    }

    public function updateFileToDB($filename, $fileHash, $type = 'CORE', $pluginTheme = null)
    {
        $fullPath = str_replace(ABSPATH, '', $filename);

        $hasUrl = 0;
        $safeUrl = 0;
        $ignore = 0;

        $coreFile = $type == 'CORE' ? 1 : 0;
        $pluginFile = $type == 'PLUGIN' ? 1 : 0;
        $themeFile = $type == 'THEME' ? 1 : 0;
        $contentFile = $type == 'WP_CONTENT' ? 1 : 0;

        $need_backup = $pluginFile == 1 || $themeFile == 1 || $contentFile == 1;

        $this->db->queryWrite('INSERT INTO '.$this->db->prefix().'madeit_sec_filelist '.
                              '(filename_md5, filename, old_md5, new_md5, file_created, file_checked, file_loaded, exist_in_orig, changed, is_safe, need_backup, in_backup, has_url, safe_url, `ignore`, core_file, plugin_file, theme_file, content_file, plugin_theme) VALUES ('.
                              "'%s', '%s', '%s', '%s', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s".
                              ") ON DUPLICATE KEY UPDATE new_md5 = '%s', has_url = %s, safe_url = %s, file_deleted = null, file_loaded = %s",
                              md5($fullPath), $fullPath, $fileHash, $fileHash, time(), null, time(), 0, 0, 1, $need_backup, 0, $hasUrl, $safeUrl, $ignore, $coreFile, $pluginFile, $themeFile, $contentFile, $pluginTheme,
                             $fileHash, $hasUrl, $safeUrl, time()
                             );
    }

    public function addHooks($settings)
    {
        add_action('madeit_security_loadfiles', [$this, 'loadfiles']);

        if (!wp_next_scheduled('madeit_security_scan_repo')) {
            wp_clear_scheduled_hook('madeit_security_scan_repo');
            wp_schedule_event(time(), 'daily', 'madeit_security_loadfiles');
        }

        if ($settings->loadDefaultSettings()['scan']['repo']['core'] || $settings->loadDefaultSettings()['scan']['repo']['theme'] || $settings->loadDefaultSettings()['scan']['repo']['plugin']) {
            $this->activateSechduler(false);
        } else {
            $this->activateSechduler(true);
        }
    }
}
