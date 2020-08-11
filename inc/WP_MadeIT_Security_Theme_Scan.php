<?php

class WP_MadeIT_Security_Theme_Scan
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function scan($fast = false)
    {
        $result = $this->scanChanges($fast);

        return $result;
    }

    public function logDebug($string)
    {
        if (MADEIT_SECURITY_DEBUG) {
            error_log('WP_MadeIT_Security_LoadFiles: '.$string);
        }
    }

    //Return array with files and there hash
    public function scanChanges($fast = false, $fileCount = 1000)
    {
        $fileData = [];
        $deletedFiles = [];
        $checkedFileList = [];
        $files = $this->db->querySelect('SELECT * FROM '.$this->db->prefix().'madeit_sec_filelist WHERE theme_file = 1 AND (file_checked = 0 OR file_checked IS NULL) ORDER BY plugin_theme LIMIT '.$fileCount);
        $i = 0;
        $lastI = 0;
        $fileList = [];
        $themeName = null;
        $themeDir = null;
        foreach ($files as $file) {
            if ($themeName == null) {
                $themeName = $file['plugin_theme'];
                $startDir = WP_CONTENT_DIR.'/themes';
                if (strpos($themeName, '/') > 0) {
                    $themeDir = str_replace(ABSPATH, '', $startDir.'/'.substr($themeName, 0, strpos($themeName, '/'))).'/';
                } else {
                    $themeDir = str_replace(ABSPATH, '', $startDir.'/'.$themeName).'/';
                }
            }
            if ($themeName != $file['plugin_theme']) {
                break;
            }
            $fileList[] = $file['filename_md5'];

            $fileName = preg_replace('/'.preg_quote($themeDir, '/').'/', '', $file['filename'], 1);
            $checkedFileList[$fileName] = $file['filename_md5'];

            if ($file['file_deleted'] == null) {
                $fileData[$fileName] = $file['new_md5'];
            } else {
                $deletedFiles[$fileName] = $file['new_md5'];
            }

            if (!$fast) {
                //Check other things in the file
            }
            $i++;

            if ($i % 50 == 0) {
                $result = get_site_transient('madeit_security_scan');
                $result['result']['theme']['files_checked'] = $result['result']['theme']['files_checked'] + ($i - $lastI);
                $lastI = $i;

                set_site_transient('madeit_security_scan', $result);
                $this->logDebug('Update result scanned '.$i.' files');

                $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set file_checked = %s WHERE filename_md5 IN ('".implode("', '", $fileList)."')", time());
                $fileList = [];
            }
        }
        $result = get_site_transient('madeit_security_scan');
        $result['result']['theme']['files_checked'] = $result['result']['theme']['files_checked'] + ($i - $lastI);
        $lastI = $i;
        set_site_transient('madeit_security_scan', $result);

        $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set file_checked = %s WHERE filename_md5 IN ('".implode("', '", $fileList)."')", time());

        if (!class_exists('WP_MadeIT_Security_Theme')) {
            include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';
        }
        $themes = new WP_MadeIT_Security_Theme();
        $themes = $themes->getThemes(false);
        $themeInfo = [];
        foreach ($themes as $theme => $value) {
            if ($value['theme'] == $themeName) {
                $themeInfo = $value;
            }
        }
        if (count($themeInfo) > 0) {
            $result = $this->postInfoToMadeIT($themeInfo, $fileData, $deletedFiles);

            $this->logDebug(print_r($result, true));

            if (isset($result['success']) && $result['success'] == true) {
                if (isset($result['changedFiles'])) {
                    foreach ($result['changedFiles'] as $file => $result) {
                        if (isset($checkedFileList[$file])) {
                            if ($result == 'File not equal') {
                                $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set is_safe = 0, reason = 'File not equal to repo' WHERE filename_md5 = %s", $checkedFileList[$file]);
                            } elseif ($result == 'File not exist') {
                                $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set is_safe = 0, reason = 'File not exist in repo' WHERE filename_md5 = %s", $checkedFileList[$file]);
                            }
                        }
                    }
                }

                if (isset($result['deletedFiles'])) {
                    foreach ($result['deletedFiles'] as $file => $result) {
                        if (isset($checkedFileList[$file])) {
                            if ($result == 'File required and changed') {
                                $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set is_safe = 0, reason = 'File required and changed' WHERE filename_md5 = %s", $checkedFileList[$file]);
                            } elseif ($result == 'File required') {
                                $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set is_safe = 0, reason = 'File required' WHERE filename_md5 = %s", $checkedFileList[$file]);
                            }
                        }
                    }
                }
            } else {
                if (isset($result['error']) && $result['error'] == 'Custom theme') {
                    //custom plugin is currently not possible to scan.
                } else {
                    //scan files later again.
                    /*$result = get_site_transient('madeit_security_scan_again');
                    if ($result == false) {
                        $result = [];
                    }
                    if (isset($themeInfo['theme'])) {
                        if (isset($result[$themeInfo['theme']])) {
                            $result[$themeInfo['theme']] = array_merge($result[$themeInfo['theme']], $checkedFileList);
                        } else {
                            $result[$themeInfo['theme']] = $checkedFileList;
                        }

                        set_site_transient('madeit_security_scan_again', $result);
                    }*/
                }
            }
        }
    }

    private function postInfoToMadeIT($themeInfo, $changedFiles, $deletedFiles)
    {
        global $wp_madeit_security_settings;

        if ($wp_madeit_security_settings === null) {
            require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Settings.php';
            $wp_madeit_security_settings = new WP_MadeIT_Security_Settings();
        }
        $settings = $wp_madeit_security_settings->loadDefaultSettings();
        $data = [
            'changedFiles' => json_encode($changedFiles),
            'deletedFiles' => json_encode($deletedFiles),
            'themeInfo'    => json_encode($themeInfo),
        ];
        if (strlen($settings['api']['key']) > 0) {
            $data['key'] = $settings['api']['key'];
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/repo-scan/theme');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return json_decode($server_output, true);
    }
}
