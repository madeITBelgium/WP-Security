<?php

if (!class_exists('WP_MadeIT_Security_Scan')) {
    include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Scan.php';
}
class WP_MadeIT_Security_Core_Scan extends WP_MadeIT_Security_Scan
{
    public function scan($fast = false)
    {
        if (!class_exists('WP_MadeIT_Security_Core')) {
            include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
        }
        $systemInfo = new WP_MadeIT_Security_Core();
        $currentWPVersion = $systemInfo->getCurrentWPVersion();

        $hash = $this->hash();

        $hashes = ['wordpress' => ['version' => $currentWPVersion, 'hash' => $hash]];

        $result = $this->postInfoToMadeIT($hashes);
        if ($fast !== true) {
            if ($result['success'] !== true || true) {
                //Do longer scan
                $fileHashes = $this->fileHash();
                $hashes = ['wordpress' => ['version' => $currentWPVersion, 'files' => $fileHashes]];
                $result = $this->postInfoToMadeIT($hashes);
            }
        }

        return $result;
    }

    //Return array with all files
    public function allFiles()
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
                    if ($file == 'wp-content') {
                        $files[$file] = $this->arrayWpContent($directory.'/'.$file);
                    } elseif (is_dir($directory.'/'.$file)) {
                        $files[$file] = $this->arrayDirectory($directory.'/'.$file);
                    } else {
                        $files[$file] = $file;
                    }
                }
            }
        }

        $dir->close();

        return $files;
    }

    public function arrayWpContent($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        $wpHeadFiles = [
            'index.php',
            /*'plugins',
            'themes',*/
        ];

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (in_array($file, $wpHeadFiles)) {
                    if (is_dir($directory.'/'.$file)) {
                        $files[$file] = $this->arrayDirectory($directory.'/'.$file);
                    } else {
                        $files[$file] = $file;
                    }
                }
            }
        }

        $dir->close();

        return $files;
    }

    //return hash op WP
    public function hash()
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
                    if ($file == 'wp-content') {
                        $files[$file] = $this->hashWpContent($directory.'/'.$file);
                    } elseif (is_dir($directory.'/'.$file)) {
                        $files[$file] = $this->hashDirectory($directory.'/'.$file);
                    } else {
                        $files[$file] = md5_file($directory.'/'.$file);
                    }
                }
            }
        }

        $dir->close();

        return md5(implode('', $files));
    }

    public function hashWpContent($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        $wpHeadFiles = [
            'index.php',
            /*'plugins',
            'themes',*/
        ];

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (in_array($file, $wpHeadFiles)) {
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

    //Return array with files and there hash
    public function fileHash()
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
                    if ($file == 'wp-content') {
                        $files[$file] = $this->fileHashWpContent($directory.'/'.$file);
                    } elseif (is_dir($directory.'/'.$file)) {
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

    public function fileHashWpContent($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        $wpHeadFiles = [
            'index.php',
            /*'plugins',
            'themes',*/
        ];

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (in_array($file, $wpHeadFiles)) {
                    if (is_dir($directory.'/'.$file)) {
                        $files[] = $this->fileHashDirectory($directory.'/'.$file);
                    } else {
                        $files[] = md5_file($directory.'/'.$file);
                    }
                }
            }
        }

        $dir->close();

        return $files;
    }

    //Check the founded hashes online.
    private function postInfoToMadeIT($plugins)
    {
        global $wp_madeit_security_settings;
        $settings = $wp_madeit_security_settings->loadDefaultSettings();
        $data = ['plugins' => json_encode($plugins)];
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
