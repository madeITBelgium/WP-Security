<?php

class WP_MadeIT_Security_Backup_Files
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function doBackup($zipFile, $contentPath, $beginPath, $inlcudeTypes = ['uploads', 'plugins', 'themes'], $excludeDirs = null)
    {
        if (extension_loaded('zip')) {
            // Initialize archive object
            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE)) {

                // Create recursive directory iterator
                /** @var SplFileInfo[] $files */
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($contentPath), RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ($files as $name => $file) {
                    // Skip directories (they would be added automatically)
                    if (!$file->isDir()) {
                        // Get real and relative path for current file
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($contentPath) + 1);

                        $fileType = $this->getTypeOfFile($relativePath);
                        if (($inlcudeTypes == null || in_array($fileType, $inlcudeTypes)) && $excludeDirs == null) {
                            // Add current file to archive
                            $zip->addFile($filePath, $beginPath.'/'.$relativePath);
                        }
                    }
                }

                // Zip archive will be created only after closing object
                return $zip->close();
            }
        }

        return false;
    }

    public function doBackupFromDB($zipFile, $inlcudeTypes = ['uploads', 'plugins', 'themes'], $excludeDirs = null)
    {
        $files = $this->db->querySelect('SELECT * FROM '.$this->db->prefix().'madeit_sec_filelist WHERE need_backup = 1 AND in_backup = 0 LIMIT 500');
        if (count($files) > 0) {
            if (extension_loaded('zip')) {
                // Initialize archive object
                $zip = new ZipArchive();
                if ($zip->open($zipFile, ZipArchive::CREATE)) {
                    $backedupFiles = [];
                    $i = 0;
                    $size = 0;
                    foreach ($files as $file) {
                        $fullPath = ABSPATH.$file['filename'];
                        if (is_file($fullPath)) {
                            $backedupFiles[] = $file['filename_md5'];
                            $size += filesize($fullPath);
                            $filename = str_replace(WP_CONTENT_DIR, '', $fullPath);
                            $zip->addFile($fullPath, $filename);
                        }
                        if ($i % 50 == 0) {
                            $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set in_backup = 1 WHERE filename_md5 IN ('".implode("', '", $backedupFiles)."')");
                            $backedupFiles = [];
                        }
                        $i++;
                    }

                    // Zip archive will be created only after closing object
                    $closeZip = $zip->close();

                    //Schedule new job
                    $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set in_backup = 1 WHERE filename_md5 IN ('".implode("', '", $backedupFiles)."')");
                    $backedupFiles = [];

                    $backupResult = get_site_transient('madeit_security_backup');
                    $backupResult['last_con_time'] = time();
                    $backupResult['files'] = $backupResult['files'] + $i;
                    $backupResult['file_size'] = $backupResult['file_size'] + $size;
                    set_site_transient('madeit_security_backup', $backupResult);
                    wp_schedule_single_event(time(), 'madeit_security_backup_run');
                    exit;
                }
            }
        }

        return true;
    }

    private function getTypeOfFile($fileRelativePath)
    {
        $types = ['uploads', 'plugins', 'themes', 'madeit-security-backup'];
        foreach ($types as $type) {
            if (strlen($fileRelativePath) > strlen($type) && substr($fileRelativePath, 0, strlen($type)) == $type) {
                return $type;
            }
        }
    }
}
