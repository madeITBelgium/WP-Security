<?php

class WP_MadeIT_Security_Backup
{
    private $timeLimit = 900;
    private $startTime = null;
    private $backup_action;

    private $defaultSettings = [];
    private $settings;
    private $db;

    public function __construct($settings, $db)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
        $this->db = $db;
    }

    public function activateSechduler($deactivate)
    {
        if ($deactivate) {
            wp_clear_scheduled_hook('madeit_security_backup');
        } else {
            if (wp_next_scheduled('madeit_security_backup') === false) {
                wp_schedule_event(time(), 'weekly', 'madeit_security_backup');
            }
        }
    }

    public function logDebug($string)
    {
        if (MADEIT_SECURITY_DEBUG) {
            error_log('WP_MadeIT_Security_Backup: '.$string);
        }
    }

    public function startBackup()
    {
        $this->logDebug('Starting backup');
        $scanResult = get_site_transient('madeit_security_scan');
        $backupResult = get_site_transient('madeit_security_backup');

        if ($backupResult === false) {
            $backupResult = [
                'time'          => time(),
                'step'          => 0,
                'done'          => false,
                'running'       => true,
                'stop'          => false,
                'last_com_time' => null,
                'preCheck'      => false,
                'check_error'   => null,
                'file'          => null,
                'result_file'   => null,
                'result_db'     => null,
                'url'           => null,
                'runtime'       => null,
                'backup_action' => str_replace('http', '', str_replace('https', '', sanitize_title(home_url('/')))).'-'.date('Y_m_d-H_i_s'),
                'total_files'   => 0,
                'files'         => 0,
                'file_size'     => 0,
            ];
        }

        if (isset($backupResult['done']) && $backupResult['stop'] == false && $backupResult['done'] == false && $backupResult['time'] <= time() - 60 * 30) {
            //Stop existing running job
            $backupResult['stop'] = true;
            set_site_transient('madeit_security_backup', $backupResult);
        } else {
            $backupResult = [
                'time'          => time(),
                'step'          => 0,
                'done'          => false,
                'running'       => true,
                'stop'          => false,
                'last_com_time' => null,
                'preCheck'      => false,
                'check_error'   => null,
                'file'          => null,
                'result_file'   => null,
                'result_db'     => null,
                'url'           => null,
                'runtime'       => null,
                'backup_action' => str_replace('http-', '', str_replace('https-', '', sanitize_title(home_url('/')))).'-'.date('Y_m_d-H_i_s'),
                'total_files'   => 0,
                'files'         => 0,
                'file_size'     => 0,
            ];
            set_site_transient('madeit_security_backup', $backupResult);

            //start job
            wp_schedule_single_event(time(), 'madeit_security_backup_run');
        }
    }

    public function stopBackup()
    {
        $this->logDebug('stopBackup');
        $result = get_site_transient('madeit_security_backup');
        $result['stop'] = true;
        set_site_transient('madeit_security_backup', $result);
    }

    public function backup()
    {
        $this->logDebug('running backup');
        ignore_user_abort(true);
        ini_set('max_execution_time', $this->timeLimit);
        ini_set('memory_limit', '1024M');

        $backupResult = get_site_transient('madeit_security_backup');
        if ($backupResult['stop'] == true) {
            $backupResult['running'] = false;
            $backupResult['done'] = true;
            set_site_transient('madeit_security_backup', $backupResult);

            return;
        }

        $this->startTime = $backupResult['time'];
        $this->backup_action = $backupResult['backup_action'];

        $zipPath = $this->backups_dir_location();
        $zipPath .= '/'.$this->getZipContentName();

        if ($backupResult['step'] == 0) {
            //Check if loading files is recent

            $this->logDebug('Check if loading files is recent');

            $scanResult = get_site_transient('madeit_security_scan');
            if (!$scanResult['done'] && !$scanResult['stop']) {
                $this->logDebug('Wait until website scan is completed, reschedule backup runner in 5min');
                //schedule event in 5 min
                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 0;
                set_site_transient('madeit_security_backup', $backupResult);
                wp_schedule_single_event((time() + 60 * 5), 'madeit_security_backup_run');

                return;
            } elseif ($scanResult['start_time'] < (time() - 60 * 60)) {
                //Start loading files and schedule event in 5 min
                require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_LoadFiles.php';
                $scan = new WP_MadeIT_Security_LoadFiles($this->settings, $this->db);
                $this->logDebug('startLoadingFiles');
                $scan->startLoadingFiles();

                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 0;
                set_site_transient('madeit_security_backup', $backupResult);
                wp_schedule_single_event((time() + 60 * 10), 'madeit_security_backup_run');

                return;
            }

            $this->deleteOlderBackups();
            $valid = $this->canICreateABackup();
            $backupResult['preCheck'] = $valid;

            $this->logDebug('Reset database');
            $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_filelist SET in_backup = 0 WHERE need_backup = 1 AND in_backup = 1');

            $count = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_filelist WHERE need_backup = 1 AND in_backup = 0');
            if (isset($count['aantal'])) {
                $this->logDebug('Files to backup: '.$count['aantal']);
                $backupResult['total_files'] = $count['aantal'];
            }

            if ($valid === true) {
                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 1;
                set_site_transient('madeit_security_backup', $backupResult);
                wp_schedule_single_event(time(), 'madeit_security_backup_run');
                exit;
            } else {
                $backupResult['done'] = false;
                $backupResult['running'] = false;
                $backupResult['stop'] = false;
                $backupResult['last_con_time'] = time();
                $backupResult['preCheck'] = false;
                $backupResult['check_error'] = $valid;
                set_site_transient('madeit_security_backup', $backupResult);
            }
        } elseif ($backupResult['step'] == 1) { //Backup files
            $this->logDebug('Backup files');

            if ($backupResult['total_files'] > $backupResult['files']) {
                $donefiles = $this->backupFiles();
                if ($donefiles > 0) {
                    exit;
                }

                //Backup files done
                $backupResult['result_file'] = $this->backups_dir_location().'/'.$this->getZipContentName();
                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 2;
                set_site_transient('madeit_security_backup', $backupResult);
                wp_schedule_single_event(time(), 'madeit_security_backup_run');
                exit;
            } else {
                //No files to backup
                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 2;
                set_site_transient('madeit_security_backup', $backupResult);
                wp_schedule_single_event(time(), 'madeit_security_backup_run');
                exit;
            }
        } elseif ($backupResult['step'] == 2) { //Backup database
            $this->logDebug('Backup database');

            $resultDb = $this->backupDatabase();

            //Backup database done
            $backupResult['last_con_time'] = time();
            $backupResult['step'] = 3;
            $backupResult['result_db'] = $this->backups_dir_location().'/'.$this->getDbScriptName();
            set_site_transient('madeit_security_backup', $backupResult);
            wp_schedule_single_event(time(), 'madeit_security_backup_run');
            exit;
        } elseif ($backupResult['step'] == 3) { //Create full zip
            $this->logDebug('Create full zip');
            $zipPath = $this->backups_dir_location().'/'.$this->getZipName();

            if ($this->createCompleteZip($zipPath)) {
                unlink($this->backups_dir_location().'/'.$this->getDbScriptName());
                unlink($this->backups_dir_location().'/'.$this->getZipContentName());

                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 4;
                set_site_transient('madeit_security_backup', $backupResult);
                wp_schedule_single_event(time(), 'madeit_security_backup_run');
                exit;
            } else {
                $backupResult['done'] = false;
                $backupResult['running'] = false;
                $backupResult['stop'] = false;
                $backupResult['last_con_time'] = time();
                set_site_transient('madeit_security_backup', $backupResult);
                wp_schedule_single_event(time(), 'madeit_security_backup_run');
                exit;
            }
        } elseif ($backupResult['step'] == 4) { //Upload zip to Made I.T.
            $this->logDebug('Uploading zip');
            $zipPath = $this->backups_dir_location().'/'.$this->getZipName();
            if ($this->defaultSettings['maintenance']['backup']) {
                $uploaded = $this->uploadBackupToMadeIT($this->getZipName(), $this->backups_dir_location(), 'FULL');
            } else {
                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 5;
                $backupResult['file'] = $zipPath;
                $backupResult['url'] = str_replace(ABSPATH, home_url('/'), $zipPath);
                set_site_transient('madeit_security_backup', $backupResult);
                $this->backup();
                exit;
            }

            $backupResult['last_con_time'] = time();
            $backupResult['step'] = 5;
            $backupResult['file'] = $zipPath;
            $backupResult['url'] = str_replace(ABSPATH, home_url('/'), $zipPath);
            set_site_transient('madeit_security_backup', $backupResult);
            wp_schedule_single_event(time(), 'madeit_security_backup_run');
            exit;
        } elseif ($backupResult['step'] == 5) { //Upload zip to FTP
            $this->logDebug('Uploading zip FTP');
            if ($this->defaultSettings['backup']['ftp']['enabled']) {
                //Upload backup to FTP server
                $this->uploadBackupToFTP($this->getZipName(), $this->backups_dir_location());
            } else {
                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 6;
                set_site_transient('madeit_security_backup', $backupResult);
                $this->backup();
                exit;
            }

            $backupResult['last_con_time'] = time();
            $backupResult['step'] = 6;
            set_site_transient('madeit_security_backup', $backupResult);
            wp_schedule_single_event(time(), 'madeit_security_backup_run');
            exit;
        } elseif ($backupResult['step'] == 6) { //Upload zip to S3
            $this->logDebug('Uploading zip S3');
            if ($this->defaultSettings['backup']['s3']['enabled']) {
                $this->uploadBackupToS3Bucket($this->getZipName(), $this->backups_dir_location());
            } else {
                $backupResult['last_con_time'] = time();
                $backupResult['step'] = 7;
                set_site_transient('madeit_security_backup', $backupResult);
                $this->backup();
                exit;
            }
            $backupResult['last_con_time'] = time();
            $backupResult['step'] = 7;
            set_site_transient('madeit_security_backup', $backupResult);
            wp_schedule_single_event(time(), 'madeit_security_backup_run');
            exit;
        } elseif ($backupResult['step'] == 7) { //Backup done
            $this->logDebug('Backup done');

            $backupResult['done'] = true;
            $backupResult['running'] = false;
            $backupResult['stop'] = false;
            $backupResult['last_con_time'] = time();
            $backupResult['runtime'] = microtime(true) - $this->startTime;
            set_site_transient('madeit_security_backup', $backupResult);
        }
    }

    private function createCompleteZip($zipPath)
    {
        if (extension_loaded('zip')) {
            // Initialize archive object
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE)) {
                if (!$zip->addFile($this->backups_dir_location().'/'.$this->getDbScriptName(), 'database.sql')) {
                    error_log('Cannot add database.sql to zip. File: '.($this->backups_dir_location().'/'.$this->getDbScriptName()), 0);
                }
                if (!$zip->addFile($this->backups_dir_location().'/'.$this->getZipContentName(), 'wp-content.zip')) {
                    error_log('Cannot add content.zip to zip. File: '.($this->backups_dir_location().'/'.$this->getZipContentName()), 0);
                }
                if (!$zip->addFile(ABSPATH.'/wp-config.php', 'wp-config.php')) {
                    error_log('Cannot add wp-config.php to zip. File: '.(ABSPATH.'/wp-config.php'), 0);
                }
                if (!$zip->addFromString('restore-config.php', $this->generateRestoreConfigFile())) {
                    error_log('Cannot add restore-config.php to zip.', 0);
                }
                if (!$zip->addFromString('restore-index.php', file_get_contents(MADEIT_SECURITY_DIR.'/inc/backup/restore_template.php'))) {
                    error_log('Cannot add restore-index.php to zip.', 0);
                }

                return $zip->close();
            }
        }

        return false;
    }

    private function backupFiles()
    {
        $this->logDebug('backupFiles()');
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup_Files.php';
        $backupFiles = new WP_MadeIT_Security_Backup_Files($this->settings, $this->db);

        $zipPath = $this->backups_dir_location();
        $zipPath .= '/'.$this->getZipContentName();

        return $backupFiles->doBackupFromDB($zipPath);
    }

    private function backupDatabase()
    {
        $this->logDebug('backupDatabase()');
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

        return 'backup_'.$this->backup_action.'.zip';
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
        $backup_dir = $this->settings->createLoggingDir();
        $this->backup_dir = $backup_dir;

        return $backup_dir;
    }

    private function generateRestoreConfigFile()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_SystemInfo.php';
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
            "\$wp_locale = '".get_locale()."';\n".
            "\$url = '".$systemInfo->getSystemInfo()['url']."';\n".
            "\$admin_url = '".$systemInfo->getSystemInfo()['admin_url']."';\n".
            "\$path = '".$systemInfo->getSystemInfo()['path']."';\n".
            "\n".
            "\$db_host = '".DB_HOST."';\n".
            "\$db_database = '".DB_NAME."';\n".
            "\$db_username = '".DB_USER."';\n".
            "\$db_password = '".DB_PASSWORD."';\n".
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
            $error = sprintf(__('The amount of memory (RAM) allowed for PHP is very low (%s Mb) - you should increase it to avoid failures due to insufficient memory (consult your web hosting company for more help)', 'wp-security-by-made-it'), round($memlim, 1));
        }
        if ($max_execution_time > 0 && $max_execution_time < 20) {
            $error = sprintf(__('The amount of time allowed for WordPress plugins to run is very low (%s seconds) - you should increase it to avoid backup failures due to time-outs (consult your web hosting company for more help - it is the max_execution_time PHP setting; the recommended value is %s seconds or more)', 'wp-security-by-made-it'), $max_execution_time, 90);
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
                $error = sprintf(__('Your free space in your hosting account is very low - only %s Mb remain', 'wp-security-by-made-it'), $freeDiskSpace);
            }
        }

        $this->logDebug('Is backup possible: '.($error == null ? 'Yes' : $error));

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
    }

    private function deleteOlderBackups()
    {
        $dir = $this->backups_dir_location();
        foreach (glob($dir.'/*') as $file) {
            if (time() - filemtime($file) >= 60 * 60 * 24 * 2) {
                if (strpos($file, 'index.html') === false && strpos($file, '.htaccess') === false && strpos($file, 'web.config') === false) {
                    unlink($file);
                }
            }
        }
    }

    private function uploadBackupToStorage($fileName, $directory, $type)
    {
        $upload = 0;
        $keepFile = false;
        if ($this->defaultSettings['maintenance']['backup']) {
            //Upload Backup to Made I.T. servers
            if ($this->uploadBackupToMadeIT($fileName, $directory, $type)) {
                $upload++;
            } else {
                $keepFile = true;
            }
        }

        if ($this->defaultSettings['backup']['ftp']['enabled']) {
            //Upload backup to FTP server
            if ($this->uploadBackupToFTP($fileName, $directory)) {
                $upload++;
            }
        }

        if ($this->defaultSettings['backup']['s3']['enabled']) {
            if ($this->uploadBackupToS3Bucket($fileName, $directory)) {
                $upload++;
            }
        }

        return $upload > 0 && !$keepFile;
    }

    private function uploadBackupToMadeIT($fileName, $directory, $type)
    {
        $fileName = untrailingslashit($this->backups_dir_location()).'/'.$fileName;

        $key = $this->defaultSettings['maintenance']['key'];
        $keepFileOnline = false;
        if (strlen($key) > 0) {
            $post = [];
            if (filesize($fileName) > 50 * 1024 * 1024) {
                $post = ['download' => str_replace(ABSPATH, home_url('/'), $fileName), 'type' => $type];
                $keepFileOnline = true;
            } else {
                if (function_exists('curl_file_create')) { // php 5.5+
                    $cFile = curl_file_create($fileName);
                } else {
                    $cFile = '@'.realpath($fileName);
                }
                $post = ['backup' => $cFile, 'type' => $type];
            }
            error_log(print_r($post, true));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/upload-backup/'.$key);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            $json = json_decode($result, true);

            return $json['success'];
        }

        return false;
    }

    private function uploadBackupToFTP($fileName, $directory)
    {
        $result = false;
        $ftp_username = $this->defaultSettings['backup']['ftp']['username'];
        $ftp_password = $this->defaultSettings['backup']['ftp']['password'];
        $ftp_server = $this->defaultSettings['backup']['ftp']['server'];
        $destination = untrailingslashit($this->defaultSettings['backup']['ftp']['destination_dir']);

        if (strlen($ftp_username) > 0 && strlen($ftp_password) > 0 && strlen($ftp_server) > 0) {
            $localFile = untrailingslashit($directory).'/'.$fileName;

            $conn_id = @ftp_connect($ftp_server);
            if ($conn_id !== false) {
                $login_result = @ftp_login($conn_id, $ftp_username, $ftp_password);
                if ($login_result !== false) {
                    if (!empty($destination)) {
                        if (@ftp_nlist($conn_id, $destination) === false) {
                            if (@ftp_mkdir($conn_id, $dir) === false) {
                                $result = false;
                            }
                        }

                        $destination = trailingslashit($destination);
                    }

                    if (@ftp_put($conn_id, $destination.$fileName, $localFile, FTP_ASCII)) {
                        $result = true;
                    }
                }
                ftp_close($conn_id);
            }
        }

        return $result;
    }

    private function uploadBackupToS3Bucket($fileName, $directory)
    {
        $result = false;
        $awsAccessKey = $this->defaultSettings['backup']['s3']['access_key'];
        $awsSecretKey = $this->defaultSettings['backup']['s3']['secret_key'];
        $bucketName = $this->defaultSettings['backup']['s3']['bucket_name'];

        if (strlen($awsSecretKey) > 0 && strlen($awsAccessKey) > 0 && strlen($bucketName) > 0) {
            require_once MADEIT_SECURITY_DIR.'/inc/backup/WP_MadeIT_Security_S3.php';
            $s3 = new WP_MadeIT_Security_S3($awsAccessKey, $awsSecretKey);

            $file = $directory.'/'.$remote_file;

            return $s3->putObject(S3::inputFile($file, false), $bucketName, $remote_file, S3::ACL_PRIVATE);
        }

        return $result;
    }

    public function addHooks()
    {
        add_action('madeit_security_backup', [$this, 'startBackup']);
        add_action('madeit_security_backup_run', [$this, 'backup']);

        if ($this->defaultSettings['backup']['enabled'] || $this->defaultSettings['maintenance']['backup'] || $this->defaultSettings['backup']['ftp']['enabled'] || $this->defaultSettings['backup']['s3']['enabled']) {
            $this->activateSechduler(false);
        } else {
            $this->activateSechduler(true);
        }
    }
}
