<?php

class WP_MadeIT_Security_Theme
{
    public function countUpdates($activeThemesOnly = false)
    {
        $themes = $this->getAllThemes(false);
        $count = 0;
        foreach ($themes as $theme) {
            if ($theme['version'] != $theme['latest_version'] && $theme['latest_version'] != null) {
                if ($theme['active'] || !$activeThemesOnly) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function getThemes($checkUpdateOnline = false)
    {
        if ($checkUpdateOnline) {
            return $this->checkThemesUpdate();
        }

        return $this->getAllThemes();
    }

    private function getAllThemes()
    {
        $themes = [];
        $installed_themes = wp_get_themes();

        foreach ($installed_themes as $theme) {
            $themes[$theme->get_stylesheet()] = [
                'name'           => $theme->get('Name'),
                'theme'          => $theme->get_stylesheet(),
                'slug'           => $theme->get_stylesheet(),
                'child_of'       => $theme->get_template(),
                'version'        => $theme->get('Version'),
                'latest_version' => null,
                'owner'          => $theme->get('Author'),
                'url'            => $theme->get('AuthorURI'),
                'repository'     => 'CUSTOM',
                'download_url'   => null,
                'active'         => $theme->get_stylesheet() == get_stylesheet(),
            ];
        }

        $updateThems = get_site_transient('update_themes');
        if (isset($updateThems->response)) {
            foreach ($updateThems->response as $style => $theme) {
                if (isset($themes[$style])) {
                    $themes[$style]['latest_version'] = $theme['new_version'];
                    $themes[$style]['repository'] = 'WORDPRESS.ORG';
                    $themes[$style]['download_url'] = $theme['package'];
                } else {
                    $themes[$style] = [
                        'name'           => null,
                        'theme'          => $style,
                        'slug'           => $style,
                        'child_of'       => null,
                        'version'        => null,
                        'latest_version' => $theme['new_version'],
                        'owner'          => null,
                        'url'            => null,
                        'repository'     => 'WORDPRESS.ORG',
                        'download_url'   => $theme['package'],
                        'active'         => $style == get_stylesheet(),
                    ];
                }
            }
        }

        return $themes;
    }

    private function checkThemesUpdate()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }
        $themes = [];
        $installed_themes = wp_get_themes();

        foreach ($installed_themes as $theme) {
            $themes[$theme->get_stylesheet()] = [
                'name'           => $theme->get('Name'),
                'theme'          => $theme->get_stylesheet(),
                'slug'           => $theme->get_stylesheet(),
                'child_of'       => $theme->get_template(),
                'version'        => $theme->get('Version'),
                'latest_version' => $theme->get('Version'),
                'owner'          => $theme->get('Author'),
                'url'            => $theme->get('AuthorURI'),
                'repository'     => 'CUSTOM',
                'download_url'   => null,
                'active'         => $theme->get_stylesheet() == get_stylesheet(),
            ];
        }

        $updateThems = $this->wp_update_themes();
        if (isset($response['themes'])) {
            foreach ($response['themes'] as $style => $theme) {
                if (isset($themes[$style])) {
                    $themes[$style]['latest_version'] = $theme['new_version'];
                    $themes[$style]['repository'] = 'WORDPRESS.ORG';
                    $themes[$style]['download_url'] = $theme['package'];
                } else {
                    $themes[$style] = [
                        'name'           => null,
                        'theme'          => $style,
                        'slug'           => $style,
                        'child_of'       => null,
                        'version'        => null,
                        'latest_version' => $theme['new_version'],
                        'owner'          => null,
                        'url'            => null,
                        'repository'     => 'WORDPRESS.ORG',
                        'download_url'   => $theme['package'],
                        'active'         => $style == get_stylesheet(),
                    ];
                }
            }
        }

        return $themes;
    }

    private function wp_update_themes()
    {
        // include an unmodified $wp_version
        include ABSPATH.WPINC.'/version.php';
        $installed_themes = wp_get_themes();
        $translations = wp_get_installed_translations('themes');
        $last_update = get_site_transient('update_themes');
        if (!is_object($last_update)) {
            $last_update = new stdClass();
        }
        $themes = $request = [];
        // Put slug of current theme into request.
        $request['active'] = get_option('stylesheet');
        foreach ($installed_themes as $theme) {
            $themes[$theme->get_stylesheet()] = [
                'Name'       => $theme->get('Name'),
                'Title'      => $theme->get('Name'),
                'Version'    => $theme->get('Version'),
                'Author'     => $theme->get('Author'),
                'Author URI' => $theme->get('AuthorURI'),
                'Template'   => $theme->get_template(),
                'Stylesheet' => $theme->get_stylesheet(),
            ];
        }

        $request['themes'] = $themes;
        $locales = array_values(get_available_languages());
        $locales = apply_filters('themes_update_check_locales', $locales);
        $locales = array_unique($locales);

        $options = [
            'timeout' => 3 + (int) (count($themes) / 10),
            'body'    => [
                'themes'       => wp_json_encode($request),
                'translations' => wp_json_encode($translations),
                'locale'       => wp_json_encode($locales),
            ],
            'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url'),
        ];
        $url = $http_url = 'http://api.wordpress.org/themes/update-check/1.1/';
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

        $new_update = new stdClass();
        if (is_array($response)) {
            $new_update->response = isset($response['themes']) ? $response['themes'] : [];
            $new_update->translations = $response['translations'];
        }
        set_site_transient('update_themes', $new_update);

        return $response;
    }
}
