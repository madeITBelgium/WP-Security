<?php

class WP_MadeIT_Security_Core_Scan
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function scan($fast = false)
    {
        if (!class_exists('WP_MadeIT_Security_Core')) {
            include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
        }
        $systemInfo = new WP_MadeIT_Security_Core();
        $currentWPVersion = $systemInfo->getCurrentWPVersion();

        $result = $this->scanChanges($currentWPVersion, $fast);

        return $result;
    }

    //Return array with files and there hash
    public function scanChanges($currentWPVersion, $fast = false, $fileCount = 1000)
    {
        $fileData = [];
        $deletedFiles = [];
        $files = $this->db->querySelect('SELECT * FROM '.$this->db->prefix().'madeit_sec_filelist WHERE core_file = 1 AND (file_checked = 0 OR file_checked IS NULL) LIMIT '.$fileCount);
        $i = 0;
        $lastI = 0;
        $fileList = [];
        foreach ($files as $file) {
            $fileList[] = $file['filename_md5'];
            if ($file['file_deleted'] == 0 || $file['file_deleted'] == null) {
                $fileData[$file['filename']] = $file['new_md5'];
            } else {
                $deletedFiles[$file['filename']] = $file['new_md5'];
            }

            if (!$fast) {
                //Check other things in the file
            }
            $i++;

            if ($i % 50 == 0) {
                $result = get_site_transient('madeit_security_scan');
                $result['result']['core']['files_checked'] = $result['result']['core']['files_checked'] + ($i - $lastI);
                $lastI = $i;
                set_site_transient('madeit_security_scan', $result);

                $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set file_checked = %s WHERE filename_md5 IN ('".implode("', '", $fileList)."')", time());
                $fileList = [];
            }
        }
        $result = get_site_transient('madeit_security_scan');
        $result['result']['core']['files_checked'] = $result['result']['core']['files_checked'] + ($i - $lastI);
        $lastI = $i;
        set_site_transient('madeit_security_scan', $result);

        $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set file_checked = %s WHERE filename_md5 IN ('".implode("', '", $fileList)."')", time());

        $this->postInfoToMadeIT($currentWPVersion, $fileData, $deletedFiles);
    }

    //Check the founded hashes online.
    private function postInfoToMadeIT($version, $changedFiles, $deletedFiles)
    {
        global $wp_madeit_security_settings;
        $settings = $wp_madeit_security_settings->loadDefaultSettings();
        $data = [
            'version'      => $version,
            'changedFiles' => json_encode($changedFiles),
            'deletedFiles' => json_encode($deletedFiles),
        ];
        if (strlen($settings['api']['key']) > 0) {
            $data['key'] = $settings['api']['key'];
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/repo-scan/core');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return json_decode($server_output, true);
    }
}
