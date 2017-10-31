<?php

class WP_MadeIT_Security_Plugin_Scan
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

    //Return array with files and there hash
    public function scanChanges($fast = false, $fileCount = 1000)
    {
        $result = get_site_transient('madeit_security_scan');
        $initialRun = $result['init_run'];
        $fileData = [];
        $deletedFiles = [];
        $checkedFileList = [];
        $files = $this->db->querySelect('SELECT * FROM '.$this->db->prefix().'madeit_sec_filelist WHERE plugin_file = 1 AND (file_checked = 0 OR file_checked IS NULL) ORDER BY plugin_theme LIMIT '.$fileCount);
        $i = 0;
        $lastI = 0;
        $fileList = [];
        $pluginName = null;
        $pluginDir = null;
        foreach ($files as $file) {
            if ($pluginName == null) {
                $pluginName = $file['plugin_theme'];
                $startDir = WP_PLUGIN_DIR;
                if (strpos($pluginName, '/') > 0) {
                    $pluginDir = str_replace(ABSPATH, '', $startDir.'/'.substr($pluginName, 0, strpos($pluginName, '/'))).'/';
                } else {
                    $pluginDir = str_replace(ABSPATH, '', $startDir.'/'.$pluginName).'/';
                }
            }
            if ($pluginName != $file['plugin_theme']) {
                break;
            }
            $fileList[] = $file['filename_md5'];

            $fileName = preg_replace('/'.preg_quote($pluginDir, '/').'/', '', $file['filename'], 1);
            $checkedFileList[$fileName] = $file['filename_md5'];

            if ($file['file_deleted'] == null) {
                $fileData[$fileName] = $file['new_md5'];
                if (!$initialRun) {
                    //Delete file in issue
                    $this->db->queryWrite('INSERT INTO '.$this->db->prefix().'madeit_sec_issues (filename_md5, filename, old_md5, new_md5, type, serverity, issue_created, shortMsg, longMsg, data) VALUES (%s, %s, %s, %s,%s, %s, %s, %s, %s, %s)', $file['filename_md5'], $file['filename'], $file['old_md5'], $file['new_md5'], 2, 3, time(), sprintf(__('The file %s is deleted', 'wp-security-by-made-it'), $file['filename']), sprintf(__('The file %s is deleted', 'wp-security-by-made-it'), $file['filename']), json_encode([]));
                }
            } else {
                $deletedFiles[$fileName] = $file['new_md5'];
                if (!$initialRun && $file['new_md5'] != $file['old_md5']) {
                    //Delete file in issue
                    $this->db->queryWrite('INSERT INTO '.$this->db->prefix().'madeit_sec_issues (filename_md5, filename, old_md5, new_md5, type, serverity, issue_created, shortMsg, longMsg, data) VALUES (%s, %s, %s, %s,%s, %s, %s, %s, %s, %s)', $file['filename_md5'], $file['filename'], $file['old_md5'], $file['new_md5'], 1, 3, time(), sprintf(__('The file %s is changed', 'wp-security-by-made-it'), $file['filename']), sprintf(__('The file %s is changed', 'wp-security-by-made-it'), $file['filename']), json_encode([]));
                }
            }

            if (!$fast && false) {
                //Check other things in the file
                $pattern = json_decode('https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/getPatterns/APIKEY', true);
                $filePath = ABSPATH.$file['filename'];
                if (file_exists($filePath)) {
                    $fileExt = '';
                    if (preg_match('/\.([a-zA-Z\d\-]{1,7})$/', $filePath, $matches)) {
                        $fileExt = strtolower($matches[1]);
                    }
                    $isPHP = false;
                    if (preg_match('/^(?:php|phtml|php\d+)$/', $fileExt)) {
                        $isPHP = true;
                    }
                    $scanForURLs = true;
                    if (preg_match('/^(?:\.htaccess|wp\-config\.php)$/', $filePath) || preg_match('/^(?:sql|tbz|tgz|gz|tar|log|err\d+)$/', $fileExt)) {
                        $scanForURLs = false;
                    }
                    $scanImages = false;
                    if (!preg_match('/^(?:jpg|jpeg|mp3|avi|m4v|gif|png)$/', $fileExt)) {
                        $scanImages = true;
                    }
                    $filesize = filesize($filePath); //Checked if too big above
                    if ($filesize > 1000000) {
                        $filesize = sprintf('%.2f', ($filesize / 1000000)).'M';
                    } else {
                        $filesize = $filesize.'B';
                    }
                    $fh = @fopen($filePath, 'r');
                    if ($fh) {
                        $totalRead = 0;
                        while (!feof($fh)) {
                            $data = fread($fh, 1 * 1024 * 1024); //read 1 megs max per chunk
                            $totalRead += strlen($data);
                            if ($totalRead < 1) {
                                break;
                            }
                            if ($isPHP) {
                                if (preg_match($patterns['sigPattern'], $data, $matches)) {
                                    $errorTitle = 'File appears to be malicious: '.$filePath;
                                    $error = 'This file appears to be installed by a hacker to perform malicious activity. If you know about this file you can choose to ignore it to exclude it from future scans. The text we found in this file that matches a known malicious file is: <strong style="color: #F00;">"'.$matches[1].'"</strong>.';
                                }
                                if (preg_match($patterns['pat2'], $data)) {
                                    $errorTitle = 'This file may contain malicious executable code: '.$filePath;
                                    $error = 'This file is a PHP executable file and contains an '.$patterns['word1'].' function and '.$patterns['word2'].' decoding function on the same line. This is a common technique used by hackers to hide and execute code. If you know about this file you can choose to ignore it to exclude it from future scans.';
                                }

                                $badStringFound = false;
                                if (strpos($data, $patterns['badstrings'][0]) !== false) {
                                    for ($i = 1; $i < count($patterns['badstrings']); $i++) {
                                        if (strpos($data, $patterns['badstrings'][$i]) !== false) {
                                            $badStringFound = $patterns['badstrings'][$i];
                                        }
                                    }
                                }
                                if ($badStringFound) {
                                    if (!$this->isSafeFile($this->path.$file)) {
                                        $title = 'This file may contain malicious executable code'.$filePath;
                                        $error = "This file is a PHP executable file and contains the word 'eval' (without quotes) and the word '".$badStringFound."' (without quotes). The eval() function along with an encoding function like the one mentioned are commonly used by hackers to hide their code. If you know about this file you can choose to ignore it to exclude it from future scans.";
                                    }
                                }
                                if ($scanForURLs) {
                                    //TODO
                                }
                            } else {
                                if ($scanForURLs) {
                                    //TODO
                                }
                            }
                            if ($totalRead > 2 * 1024 * 1024) {
                                //Break loop
                            }
                        }
                        fclose($fh);
                    }
                }
            }
            $i++;

            if ($i % 50 == 0) {
                $result = get_site_transient('madeit_security_scan');
                $result['result']['plugin']['files_checked'] = $result['result']['plugin']['files_checked'] + ($i - $lastI);
                $lastI = $i;
                set_site_transient('madeit_security_scan', $result);

                $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set file_checked = %s WHERE filename_md5 IN ('".implode("', '", $fileList)."')", time());
                $fileList = [];
            }
        }
        $result = get_site_transient('madeit_security_scan');
        $result['result']['plugin']['files_checked'] = $result['result']['plugin']['files_checked'] + ($i - $lastI);
        $lastI = $i;
        set_site_transient('madeit_security_scan', $result);

        $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set file_checked = %s WHERE filename_md5 IN ('".implode("', '", $fileList)."')", time());

        $pluginInfo = [];
        if (!class_exists('WP_MadeIT_Security_Plugin')) {
            include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        }
        $plugins = new WP_MadeIT_Security_Plugin();
        $plugins = $plugins->getPlugins(false);
        foreach ($plugins as $plugin => $value) {
            if (substr($plugin, 0, strpos($plugin, '/')) == $pluginName) {
                $pluginInfo = $value;
            }
        }

        $result = $this->postInfoToMadeIT($pluginInfo, $fileData, $deletedFiles);
        if (isset($result['success']) && $result['success'] == true) {
            if (isset($result['changedFiles'])) {
                foreach ($result['changedFiles'] as $file => $result) {
                    if (isset($checkedFileList[$file])) {
                        if ($result == 'File not equal') {
                            $this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_filelist set is_safe = 0, reason = 'File not equal to repo' WHERE filename_md5 = %s", $checkedFileList[$file]);

                            //$this->db->queryWrite('UPDATE '.$this->db->prefix()."madeit_sec_issues type = %s, serverity = %s, shortMsg = %s, longMsg = %s, data = %s) WHERE filename_md5 = %s ORDER BY id DESC LIMIT 1", 3, 4, sprintf(__('The file %s is deleted', 'wp-security-by-made-it'), $file), sprintf(__('The file %s is changed compared to the repo version', 'wp-security-by-made-it'), $file), json_encode([]), $checkedFileList[$file]);
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
            if (isset($result['error']) && $result['error'] == 'Custom plugin') {
                //custom plugin is currently not possible to scan.
            } else {
                //scan files later again.
                $result = get_site_transient('madeit_security_scan_again');
                if ($result == false) {
                    $result = [];
                }
                if (isset($pluginInfo['slug'])) {
                    if (isset($result[$pluginInfo['slug']])) {
                        $result[$pluginInfo['slug']] = array_merge($result[$pluginInfo['slug']], $checkedFileList);
                    } else {
                        $result[$pluginInfo['slug']] = $checkedFileList;
                    }
                    set_site_transient('madeit_security_scan_again', $result);
                }
            }
        }
    }

    private function postInfoToMadeIT($pluginInfo, $changedFiles, $deletedFiles)
    {
        global $wp_madeit_security_settings;
        $settings = $wp_madeit_security_settings->loadDefaultSettings();
        $data = [
            'changedFiles' => json_encode($changedFiles),
            'deletedFiles' => json_encode($deletedFiles),
            'pluginInfo'   => $pluginInfo,
        ];
        if (strlen($settings['api']['key']) > 0) {
            $data['key'] = $settings['api']['key'];
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/repo-scan/plugin');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return json_decode($server_output, true);
    }
}
