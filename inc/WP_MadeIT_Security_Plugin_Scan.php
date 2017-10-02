<?php

if (!class_exists('WP_MadeIT_Security_Scan')) {
    include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Scan.php';
}
class WP_MadeIT_Security_Plugin_Scan extends WP_MadeIT_Security_Scan
{
    public function scan($fast = false)
    {
        if (!class_exists('WP_MadeIT_Security_Plugin')) {
            include_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        }
        $plugins = new WP_MadeIT_Security_Plugin();
        $plugins = $plugins->getPlugins(false);
        $hashes = [];
        foreach ($plugins as $key => $value) {
            $hash = $this->hashPlugin($key);
            $hashes[$value['slug']] = ['version' => $value['version'], 'hash' => $hash];
        }

        $result = $this->postInfoToMadeIT($hashes);
        if ($fast !== true) {
            if ($result['success'] !== true) {
                //Do longer scan
                $newResult = ['success' => true, 'plugins' => [], 'count_plugin_errors' => 0];
                foreach ($plugins as $key => $value) {
                    if (isset($result['plugins'][$value['slug']]) && $result['plugins'][$value['slug']] === false) {
                        $fileHashes = $this->fileHashPlugin($key);
                        $hashes = [];
                        $hashes[$value['slug']] = ['version' => $value['version'], 'files' => $fileHashes];
                        $tussenResult = $this->postInfoToMadeIT($hashes);

                        $newResult['success'] = $newResult['success'] && $tussenResult['success'];
                        $newResult['plugins'] = array_merge($newResult['plugins'], $tussenResult['plugins']);
                        
                        $errors = count($tussenResult['plugins']);
                        foreach($tussenResult['plugins'] as $plugin => $files) {
                            foreach($files as $file => $error) {
                                if($this->isFileIgnored($value['slug'], $file)) {
                                    $errors--;
                                }
                            }
                        }
                        if($errors > 0) {
                            $newResult['count_plugin_errors']++;
                        }
                    }
                }
                $result = $newResult;
            }
        }

        return $result;
    }

    public function hashPlugin($plugin)
    {
        $startDir = WP_PLUGIN_DIR;
        $pluginDir = $startDir.'/'.substr($plugin, 0, strpos($plugin, '/'));
        $exclude = $this->searchExcludes(substr($plugin, 0, strpos($plugin, '/')));
        $result = $this->hashDirectory($pluginDir, $exclude);

        return $result;
    }

    public function fileHashPlugin($plugin)
    {
        $startDir = WP_PLUGIN_DIR;
        $pluginDir = $startDir.'/'.substr($plugin, 0, strpos($plugin, '/'));
        $exclude = $this->searchExcludes(substr($plugin, 0, strpos($plugin, '/')));
        $result = $this->fileHashDirectory($pluginDir, $exclude);

        return $result;
    }

    private function searchExcludes($plugin)
    {
        if ($plugin == 'bwp-minify') {
            return ['cache'];
        }
        if ($plugin == 'forms-by-made-it') {
            return ['.git'];
        }

        return [];
    }

    private function postInfoToMadeIT($plugins)
    {
        global $wp_madeit_security_settings;
        $settings = $wp_madeit_security_settings->loadDefaultSettings();
        $data = ['plugins' => json_encode($plugins)];
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
