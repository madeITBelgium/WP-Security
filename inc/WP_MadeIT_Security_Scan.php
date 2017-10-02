<?php

class WP_MadeIT_Security_Scan
{
    public function fullScanAgainstRepoFiles()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core_Scan.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin_Scan.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme_Scan.php';

        $core = new WP_MadeIT_Security_Core_Scan();
        $plugin = new WP_MadeIT_Security_Plugin_Scan();
        $theme = new WP_MadeIT_Security_Theme_Scan();
        $coreResult = $core->scan();
        $pluginResult = $plugin->scan();
        $themeResult = $theme->scan();

        $result = ['core' => $coreResult, 'plugin' => $pluginResult, 'theme' => $themeResult, 'time' => time()];
        set_site_transient('madeit_security_repo_scan', $result);

        return $result;
    }

    public function activateSechduler($deactivate)
    {
        if ($deactivate) {
            wp_clear_scheduled_hook('madeit_security_scan_repo');
        } else {
            if (!wp_next_scheduled('madeit_security_scan_repo')) {
                wp_schedule_event(time(), 'daily', 'madeit_security_scan_repo');
            }
        }
    }

    public function arrayDirectory($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (is_dir($directory.'/'.$file)) {
                    $files[$file] = $this->arrayDirectory($directory.'/'.$file);
                } else {
                    $files[$file] = $file;
                }
            }
        }

        $dir->close();

        return $files;
    }

    public function hashDirectory($directory, $exclude = [])
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (!in_array($file, $exclude)) {
                    if (is_dir($directory.'/'.$file)) {
                        $files[] = $this->hashDirectory($directory.'/'.$file);
                    } else {
                        $files[] = md5_file($directory.'/'.$file);
                    }
                }
            }
        }

        $dir->close();

        return md5(implode('', $files));
    }

    public function fileHashDirectory($directory, $exclude = [])
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (!in_array($file, $exclude)) {
                    if (is_dir($directory.'/'.$file)) {
                        $files[$file] = $this->fileHashDirectory($directory.'/'.$file);
                    } else {
                        $files[$file] = md5_file($directory.'/'.$file);
                    }
                }
            }
        }

        $dir->close();

        return $files;
    }

    public function addHooks($settings)
    {
        add_action('madeit_security_scan_repo', [$this, 'fullScanAgainstRepoFiles']);

        if ($settings->loadDefaultSettings()['scan']['repo']['core'] || $settings->loadDefaultSettings()['scan']['repo']['theme'] || $settings->loadDefaultSettings()['scan']['repo']['plugin']) {
            $this->activateSechduler(false);
        } else {
            $this->activateSechduler(true);
        }
    }
    
    public function isFileIgnored($plugin, $file) {
        $ignoreData = get_site_transient('madeit_security_ignore_scan');
        return isset($ignoreData[$plugin][$file]);
    }
}
