<?php

if (!class_exists('WP_MadeIT_Security_Scan')) {
    include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Scan.php';
}
class WP_MadeIT_Security_Theme_Scan extends WP_MadeIT_Security_Scan
{
    public function scan($fast = false)
    {
        if (!class_exists('WP_MadeIT_Security_Theme')) {
            include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';
        }
        $themes = new WP_MadeIT_Security_Theme();
        $themes = $themes->getThemes(false);
        $hashes = [];
        foreach ($themes as $key => $value) {
            $hash = $this->hashTheme($value['theme']);
            $hashes[$value['theme']] = ['version' => $value['version'], 'hash' => $hash];
        }
        $result = $this->postInfoToMadeIT($hashes);
        if ($fast !== true) {
            if ($result['success'] !== true) {
                //Do longer scan
                $hashes = [];
                foreach ($themes as $key => $value) {
                    if ($result['themes'][$value['theme']] === false) {
                        $fileHashes = $this->fileHashTheme($value['theme']);
                        $hashes[$value['theme']] = ['version' => $value['version'], 'files' => $fileHashes];
                    }
                }
                $result = $this->postInfoToMadeIT($hashes);
            }
        }

        return $result;
    }

    public function hashTheme($theme)
    {
        $startDir = WP_CONTENT_DIR.'/themes';
        $themeDir = $startDir.'/'.substr($theme, 0, strpos($theme, '/'));
        $result = $this->hashDirectory($themeDir);

        return $result;
    }

    public function fileHashTheme($theme)
    {
        $startDir = WP_CONTENT_DIR.'/themes';
        $theme = strpos($theme, '/') > 0 ? substr($theme, 0, strpos($theme, '/')) : $theme;
        $themeDir = $startDir.'/'.$theme;
        $result = $this->fileHashDirectory($themeDir);

        return $result;
    }

    private function postInfoToMadeIT($themes)
    {
        global $wp_madeit_security_settings;
        $settings = $wp_madeit_security_settings->loadDefaultSettings();
        $data = ['themes' => json_encode($themes)];
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
