<?php

class WP_MadeIT_Security_Backup
{
    private $timeLimit = 900;
    private $startTime = null;
    private $backup_action;

    private $defaultSettings = [];
    private $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
    }

    public function activateSechduler($deactivate)
    {
        if ($deactivate) {
            wp_clear_scheduled_hook('madeit_security_backup');
        } else {
            if (!wp_next_scheduled('madeit_security_backup')) {
                wp_schedule_event(time(), 'daily', 'madeit_security_backup');
            }
        }
    }

    public function create_backup()
    {
        return $this->backup();
    }

    private function backup()
    {
        ini_set('max_execution_time', $this->timeLimit);
        ini_set('memory_limit', '1024M');

        $this->startTime = microtime(true);
        $this->deleteOlderBackups();

        $zipPath = $this->backups_dir_location();
        $zipPath .= '/'.$this->getZipContentName();
        $valid = $this->canICreateABackup();

        $result = false;
        if ($valid === true) {
            $resultFile = $this->backupFiles();
            $resultDb = $this->backupDatabase();

            if ($resultFile && $resultDb) {
                $zipPath = $this->backups_dir_location();
                $zipPath .= '/'.$this->getZipName();

                $this->createCompleteZip($zipPath);
            }
        }
        $data = [
            'time'        => time(),
            'status'      => $resultFile,
            'preCheck'    => $valid === true,
            'check_error' => $valid === true ? null : $valid,
            'file'        => $zipPath,
            'result_file' => $resultFile,
            'result_db'   => $resultDb,
            'url'         => str_replace(ABSPATH, home_url('/'), $zipPath),
            'runtime'     => microtime(true) - $this->startTime,
        ];
        set_site_transient('madeit_security_backup', $data);

        return $data;
    }

    private function createCompleteZip($zipPath)
    {
        if (extension_loaded('zip')) {
            // Initialize archive object
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE)) {
                $zip->addFile('database.sql', $this->backups_dir_location().'/'.$this->getDbScriptName());
                $zip->addFile('wp-content.zip', $this->backups_dir_location().'/'.$this->getZipContentName());
                $zip->addFile('wp-config.php', ABSPATH.'/wp-config.php');
                $zip->addFromString('restore-config.php', $this->generateRestoreConfigFile());

                return $zip->close();
            }
        }

        return false;
    }

    private function backupFiles()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup_Files.php';
        $backupFiles = new WP_MadeIT_Security_Backup_Files();

        $zipPath = $this->backups_dir_location();
        $zipPath .= '/'.$this->getZipContentName();
        $contentPath = WP_CONTENT_DIR;
        $contentDir = substr($contentPath, strlen(ABSPATH));

        $result = $backupFiles->doBackup($zipPath, $contentPath, $contentDir, ['uploads', 'plugins', 'themes']);

        return $result;
    }

    private function backupDatabase()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup_Database.php';
        $backupDatabase = new WP_MadeIT_Security_Backup_Database('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);

        $dbPath = $this->backups_dir_location();
        $dbPath .= '/'.$this->getDbScriptName();

        $backupDatabase->start($dbPath);

        return true;
    }

    private function getZipContentName()
    {
        if ($this->backup_action == null) {
            $this->backup_action = time().rand();
        }

        return 'backup_'.$this->backup_action.'_wp-content.zip';
    }

    private function getZipName()
    {
        if ($this->backup_action == null) {
            $this->backup_action = time().rand();
        }

        return 'backup_'.$this->backup_action.'_backup.zip';
    }

    private function getDbScriptName()
    {
        if ($this->backup_action == null) {
            $this->backup_action = time().rand();
        }

        return 'backup_'.$this->backup_action.'_database.sql';
    }

    private function backups_dir_location($allow_cache = true)
    {
        if ($allow_cache && !empty($this->backup_dir)) {
            return $this->backup_dir;
        }

        $backup_dir = WP_CONTENT_DIR.'/madeit-security-backup';

        // Check for the existence of the dir and prevent enumeration
        // index.php is for a sanity check - make sure that we're not somewhere unexpected
        if ((!is_dir($backup_dir) || !is_file($backup_dir.'/index.html') || !is_file($backup_dir.'/.htaccess')) && !is_file($backup_dir.'/index.php') || !is_file($backup_dir.'/web.config')) {
            mkdir($backup_dir, 0775, true);
            file_put_contents($backup_dir.'/index.html', '<html><body><a href="https://www.madeit.be">WordPress backups by Security by Made I.T.</a></body></html>');
            if (!is_file($backup_dir.'/.htaccess')) {
                file_put_contents($backup_dir.'/.htaccess', 'deny from all');
            }
            if (!is_file($backup_dir.'/web.config')) {
                file_put_contents($backup_dir.'/web.config', "<configuration>\n<system.webServer>\n<authorization>\n<deny users=\"*\" />\n</authorization>\n</system.webServer>\n</configuration>\n");
            }
        }

        $this->backup_dir = $backup_dir;

        return $backup_dir;
    }

    private function generateRestoreConfigFile()
    {
        require_once 'WP_MadeIT_Security_SystemInfo.php';
        $systemInfo = new WP_MadeIT_Security_SystemInfo();

        $result = "<?php\n".
            "/***************************************************************************/\n".
            "/*                                                                         */\n".
            "/* This is the config file to restore/duplicate the website in this backup */\n".
            "/*                                                                         */\n".
            "/***************************************************************************/\n".
            "\n".
            "\$php_version = '".$systemInfo->getSystemInfo()['php_version']."';\n".
            "\$mysql_version = '".$systemInfo->getSystemInfo()['mysql_version']."';\n".
            "\$wp_version = '".$systemInfo->getSystemInfo()['wp_version']."';\n".
            "\$url = '".$systemInfo->getSystemInfo()['url']."';\n".
            "\$admin_url = '".$systemInfo->getSystemInfo()['admin_url']."';\n".
            "\$path = '".$systemInfo->getSystemInfo()['path']."';\n".
            '?>';

        return $result;
    }

    private function canICreateABackup()
    {
        $error = null;
        $safe_mode = $this->detect_safe_mode();

        //Check memory
        $memory_limit = ini_get('memory_limit');
        $memory_usage = round(memory_get_usage(false) / 1048576, 1);
        $memory_usage2 = round(memory_get_usage(true) / 1048576, 1);

        set_time_limit($this->timeLimit);
        $max_execution_time = (int) ini_get('max_execution_time');

        $memlim = $this->memory_check_current();
        if ($memlim < 65 && $memlim > 0) {
            $error = sprintf(__('The amount of memory (RAM) allowed for PHP is very low (%s Mb) - you should increase it to avoid failures due to insufficient memory (consult your web hosting company for more help)', 'madeit_security'), round($memlim, 1));
        }
        if ($max_execution_time > 0 && $max_execution_time < 20) {
            $error = sprintf(__('The amount of time allowed for WordPress plugins to run is very low (%s seconds) - you should increase it to avoid backup failures due to time-outs (consult your web hosting company for more help - it is the max_execution_time PHP setting; the recommended value is %s seconds or more)', 'madeit_security'), $max_execution_time, 90);
        }

        //can zip
        $canZip = extension_loaded('zip');
        if (!$canZip) {
            $error = __('The webserver has no zip module.', '');
        }

        //Check diskspace
        $hosting_bytes_free = $this->get_hosting_disk_quota_free();
        if (is_array($hosting_bytes_free)) {
            $perc = round(100 * $hosting_bytes_free[1] / (max($hosting_bytes_free[2], 1)), 1);
            $freeDiskSpace = round($hosting_bytes_free[3] / 1048576, 1);

            if ($hosting_bytes_free[3] < 1048576 * 50) {
                $error = sprintf(__('Your free space in your hosting account is very low - only %s Mb remain', 'madeit_security'), $freeDiskSpace);
            }
        }

        return $error == null ? true : $error;
    }

    public function detect_safe_mode()
    {
        return (ini_get('safe_mode') && strtolower(ini_get('safe_mode')) != 'off') ? 1 : 0;
    }

    private function memory_check_current($memory_limit = false)
    {
        // Returns in megabytes
        if ($memory_limit == false) {
            $memory_limit = ini_get('memory_limit');
        }
        $memory_limit = rtrim($memory_limit);
        $memory_unit = $memory_limit[strlen($memory_limit) - 1];
        if ((int) $memory_unit == 0 && $memory_unit !== '0') {
            $memory_limit = substr($memory_limit, 0, strlen($memory_limit) - 1);
        } else {
            $memory_unit = '';
        }
        switch ($memory_unit) {
            case '':
                $memory_limit = floor($memory_limit / 1048576);
            break;
            case 'K':
            case 'k':
                $memory_limit = floor($memory_limit / 1024);
            break;
            case 'G':
                $memory_limit = $memory_limit * 1024;
            break;
            case 'M':
                //assumed size, no change needed
            break;
        }

        return $memory_limit;
    }

    private function get_hosting_disk_quota_free()
    {
        return false;

        try {
            if (!is_dir('/usr/local/cpanel') || $this->detect_safe_mode() || !function_exists('popen') || (!is_executable('/usr/local/bin/perl') && !is_executable('/usr/local/cpanel/3rdparty/bin/perl'))) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        $perl = (is_executable('/usr/local/cpanel/3rdparty/bin/perl')) ? '/usr/local/cpanel/3rdparty/bin/perl' : '/usr/local/bin/perl';
        $exec = "UPDRAFTPLUSKEY=updraftplus $perl ".MADEIT_SECURITY_DIR.'/inc/get-cpanel-quota-usage.pl';
        $handle = popen($exec, 'r');
        if (!is_resource($handle)) {
            return false;
        }

        $found = false;
        $lines = 0;
        while (false === $found && !feof($handle) && $lines < 100) {
            $lines++;
            $w = fgets($handle);
            // Used, limit, remain
            if (preg_match('/RESULT: (\d+) (\d+) (\d+) /', $w, $matches)) {
                $found = true;
            }
        }
        $ret = pclose($handle);
        if (false === $found || $ret != 0) {
            return false;
        }
        if ((int) $matches[2] < 100 || ($matches[1] + $matches[3] != $matches[2])) {
            return false;
        }

        return $matches;
    }

    private function deleteOlderBackups()
    {
        $dir = $this->backups_dir_location();
        foreach (glob($dir.'/*') as $file) {
            if (strpos($file, 'index.html') === false && strpos($file, '.htaccess') && strpos($file, 'web.config') && time() - filectime($file) > 60 * 60 * 24 * 7) {
                unlink($file);
            }
        }
    }

    public function addHooks()
    {
        add_action('madeit_security_backup', [$this, 'create_backup']);

        if ($this->defaultSettings['maintenance']['backup']) {
            $this->activateSechduler(false);
        } else {
            $this->activateSechduler(true);
        }
    }
}
