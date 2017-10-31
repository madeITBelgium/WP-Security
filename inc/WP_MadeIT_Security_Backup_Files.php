<?php

class WP_MadeIT_Security_Backup_Files
{
    private $db;
    private $settings;
    private $defaultSettings = [];

    public function __construct($settings, $db)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
        $this->db = $db;
    }

    public function doBackupFromDB($zipFile, $inlcudeTypes = ['uploads', 'plugins', 'themes'], $excludeDirs = null)
    {
        $fileToBackup = $this->defaultSettings['backup']['files'];
        $files = $this->db->querySelect('SELECT * FROM '.$this->db->prefix().'madeit_sec_filelist WHERE need_backup = 1 AND in_backup = 0 LIMIT %d', $fileToBackup);
        if (count($files) > 0) {
            if (extension_loaded('zip')) {
                // Initialize archive object
                $zip = new ZipArchive();
                if ($zip->open($zipFile, ZipArchive::CREATE)) {
                    $backedupFiles = [];
                    $i = 0;
                    $filesDoneNow = 0;

                    $backupResult = get_site_transient('madeit_security_backup');
                    $filesCount = $backupResult['files'];
                    $size = $backupResult['file_size'];
                    $totalFiles = $backupResult['total_files'];
                    foreach ($files as $file) {
                        $fullPath = ABSPATH.$file['filename'];
                        if (is_file($fullPath)) {
                            $backedupFiles[] = $file['filename_md5'];
                            $size += filesize($fullPath);
                            $filesCount++;
                            $filename = str_replace(WP_CONTENT_DIR, '', $fullPath);
                            $zip->addFile($fullPath, $filename);
                            $filesDoneNow++;
                        } else {
                            $filesCount++;
                        }
                        if ($i % 50 == 0) {
                            $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set in_backup = 1 WHERE filename_md5 IN ('".implode("', '", $backedupFiles)."')");
                            $backedupFiles = [];

                            $backupResult = get_site_transient('madeit_security_backup');
                            $backupResult['last_con_time'] = time();
                            $backupResult['files'] = $filesCount;
                            $backupResult['file_size'] = $size;
                            $backupResult['total_files'] = $totalFiles;
                            set_site_transient('madeit_security_backup', $backupResult);
                        }
                        $i++;
                    }

                    // Zip archive will be created only after closing object
                    $closeZip = $zip->close();

                    //Schedule new job
                    $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set in_backup = 1 WHERE filename_md5 IN ('".implode("', '", $backedupFiles)."')");
                    $backedupFiles = [];

                    //Start new job
                    $backupResult = get_site_transient('madeit_security_backup');
                    $backupResult['last_con_time'] = time();
                    $backupResult['files'] = $filesCount;
                    $backupResult['file_size'] = $size;
                    $backupResult['total_files'] = $totalFiles;
                    set_site_transient('madeit_security_backup', $backupResult);
                    wp_schedule_single_event(time(), 'madeit_security_backup_run');
                    return $filesDoneNow;
                }
            }
        }
        return 0;
    }
}
