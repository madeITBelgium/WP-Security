<?php

class WP_MadeIT_Security_Plugin
{
    public function countUpdates($activePluginsOnly = false)
    {
        $plugins = $this->getPlugins(false);
        $count = 0;
        foreach ($plugins as $plugin) {
            if (version_compare($plugin['version'], $plugin['latest_version'], '<') && $plugin['latest_version'] != null) {
                if ($plugin['active'] || !$activePluginsOnly) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function getActivePlugins()
    {
        $plugins = $this->getPlugins(false);
        $activePlugins = [];
        foreach ($plugins as $plugin) {
            if ($plugin['active']) {
                $activePlugins[] = $plugin;
            }
        }

        return $activePlugins;
    }

    public function getPlugins($checkUpdateOnline = false)
    {
        if ($checkUpdateOnline) {
            return $this->checkPluginsUpdate();
        }

        return $this->getAllPlugins();
    }

    private function getAllPlugins()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();

        $plugins = [];
        foreach ($all_plugins as $key => $plugin) {
            $plugins[$key] = [
                'name'           => $plugin['Name'],
                'plugin'         => $key,
                'slug'           => null,
                'url'            => $plugin['PluginURI'],
                'owner'          => $plugin['Author'],
                'version'        => $plugin['Version'],
                'latest_version' => $plugin['Version'],
                'repository'     => 'CUSTOM',
                'download_url'   => null,
                'active'         => is_plugin_active($key),
            ];
        }

        $wp_plugins = get_site_transient('update_plugins');
        //print_r($wp_plugins); exit;
        if (isset($wp_plugins->response)) {
            foreach ($wp_plugins->response as $plugin) {
                $plugin = json_decode(json_encode($plugin), true);
                if (isset($plugins[$plugin['plugin']])) {
                    $plugins[$plugin['plugin']]['slug'] = $plugin['slug'];
                    $plugins[$plugin['plugin']]['latest_version'] = $plugin['new_version'];
                    $plugins[$plugin['plugin']]['repository'] = 'WORDPRESS.ORG';
                    $plugins[$plugin['plugin']]['download_url'] = $plugin['package'];
                } else {
                    $plugins[$plugin['plugin']] = [
                        'name'           => null,
                        'plugin'         => $plugin['plugin'],
                        'slug'           => $plugin['slug'],
                        'url'            => null,
                        'owner'          => null,
                        'version'        => null,
                        'latest_version' => $plugin['new_version'],
                        'repository'     => 'WORDPRESS.ORG',
                        'download_url'   => $plugin['package'],
                        'active'         => is_plugin_active($plugin['plugin']),
                    ];
                }
            }
        }

        if (isset($wp_plugins->no_update)) {
            foreach ($wp_plugins->no_update as $plugin) {
                $plugin = (array) $plugin;
                if (isset($plugins[$plugin['plugin']])) {
                    $plugins[$plugin['plugin']]['slug'] = $plugin['slug'];
                    $plugins[$plugin['plugin']]['latest_version'] = $plugin['new_version'];
                    $plugins[$plugin['plugin']]['repository'] = 'WORDPRESS.ORG';
                    $plugins[$plugin['plugin']]['download_url'] = $plugin['package'];
                } else {
                    $plugins[$plugin['plugin']] = [
                        'name'           => null,
                        'plugin'         => $plugin['plugin'],
                        'slug'           => $plugin['slug'],
                        'url'            => null,
                        'owner'          => null,
                        'version'        => null,
                        'latest_version' => $plugin['new_version'],
                        'repository'     => 'WORDPRESS.ORG',
                        'download_url'   => $plugin['package'],
                        'active'         => is_plugin_active($plugin['plugin']),
                    ];
                }
            }
        }

        return $plugins;
    }

    private function checkPluginsUpdate()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();

        $plugins = [];
        foreach ($all_plugins as $key => $plugin) {
            $plugins[$key] = [
                'name'           => $plugin['Name'],
                'plugin'         => $key,
                'slug'           => null,
                'url'            => $plugin['PluginURI'],
                'owner'          => $plugin['Author'],
                'version'        => $plugin['Version'],
                'latest_version' => $plugin['Version'],
                'repository'     => 'CUSTOM',
                'download_url'   => null,
                'active'         => is_plugin_active($key),
            ];
        }

        $wp_plugins = $this->wp_update_plugins();
        if (isset($wp_plugins['plugins'])) {
            foreach ($wp_plugins['plugins'] as $plugin) {
                $plugin = (array) $plugin;
                if (isset($plugins[$plugin['plugin']])) {
                    $plugins[$plugin['plugin']]['slug'] = $plugin['slug'];
                    $plugins[$plugin['plugin']]['latest_version'] = $plugin['new_version'];
                    $plugins[$plugin['plugin']]['repository'] = 'WORDPRESS.ORG';
                    $plugins[$plugin['plugin']]['download_url'] = $plugin['package'];
                } else {
                    $plugins[$plugin['plugin']] = [
                        'name'           => null,
                        'plugin'         => $plugin['plugin'],
                        'slug'           => $plugin['slug'],
                        'url'            => null,
                        'owner'          => null,
                        'version'        => null,
                        'latest_version' => $plugin['new_version'],
                        'repository'     => 'WORDPRESS.ORG',
                        'download_url'   => $plugin['package'],
                        'active'         => is_plugin_active($plugin['plugin']),
                    ];
                }
            }
        }

        if (isset($wp_plugins['no_update'])) {
            foreach ($wp_plugins['no_update'] as $plugin) {
                $plugin = (array) $plugin;
                if (isset($plugins[$plugin['plugin']])) {
                    $plugins[$plugin['plugin']]['slug'] = $plugin['slug'];
                    $plugins[$plugin['plugin']]['latest_version'] = $plugin['new_version'];
                    $plugins[$plugin['plugin']]['repository'] = 'WORDPRESS.ORG';
                    $plugins[$plugin['plugin']]['download_url'] = $plugin['package'];
                } else {
                    $plugins[$plugin['plugin']] = [
                        'name'           => null,
                        'plugin'         => $plugin['plugin'],
                        'slug'           => $plugin['slug'],
                        'url'            => null,
                        'owner'          => null,
                        'version'        => null,
                        'latest_version' => $plugin['new_version'],
                        'repository'     => 'WORDPRESS.ORG',
                        'download_url'   => $plugin['package'],
                        'active'         => is_plugin_active($plugin['plugin']),
                    ];
                }
            }
        }

        return $plugins;
    }

    private function wp_update_plugins()
    {
        // include an unmodified $wp_version
        include ABSPATH.WPINC.'/version.php';
        // If running blog-side, bail unless we've not checked in the last 12 hours
        if (!function_exists('get_plugins')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }
        $plugins = get_plugins();

        $translations = wp_get_installed_translations('plugins');

        $active = get_option('active_plugins', []);

        $to_send = compact('plugins', 'active');

        $locales = array_values(get_available_languages());
        $locales = apply_filters('plugins_update_check_locales', $locales);
        $locales = array_unique($locales);

        $options = [
            'timeout' => 3 + (int) (count($plugins) / 10),
            'body'    => [
                'plugins'      => wp_json_encode($to_send),
                'translations' => wp_json_encode($translations),
                'locale'       => wp_json_encode($locales),
                'all'          => wp_json_encode(true),
            ],
            'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url'),
        ];
        $url = $http_url = 'http://api.wordpress.org/plugins/update-check/1.1/';
        if ($ssl = wp_http_supports(['ssl'])) {
            $url = set_url_scheme($url, 'https');
        }
        $raw_response = wp_remote_post($url, $options);
        if ($ssl && is_wp_error($raw_response)) {
            $raw_response = wp_remote_post($http_url, $options);
        }
        if (is_wp_error($raw_response) || 200 != wp_remote_retrieve_response_code($raw_response)) {
            return;
        }
        $response = json_decode(wp_remote_retrieve_body($raw_response), true);
        foreach ($response['plugins'] as &$plugin) {
            $plugin = (object) $plugin;
            if (isset($plugin->compatibility)) {
                $plugin->compatibility = (object) $plugin->compatibility;
                foreach ($plugin->compatibility as &$data) {
                    $data = (object) $data;
                }
            }
        }
        unset($plugin, $data);
        foreach ($response['no_update'] as &$plugin) {
            $plugin = (object) $plugin;
        }
        unset($plugin);
        $new_option = new stdClass();
        if (is_array($response)) {
            $new_option->response = empty($response['plugins']) ? [] : $response['plugins'];
            $new_option->translations = $response['translations'];
            // TODO: Perhaps better to store no_update in a separate transient with an expiry?
            $new_option->no_update = $response['no_update'];
        } else {
            $new_option->response = [];
            $new_option->translations = [];
            $new_option->no_update = [];
        }
        set_site_transient('update_plugins', $new_option);

        return $response;
    }
}
