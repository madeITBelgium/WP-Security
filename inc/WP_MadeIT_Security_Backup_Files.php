<?php

class WP_MadeIT_Security_Backup_Files
{
    public function __construct()
    {
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
