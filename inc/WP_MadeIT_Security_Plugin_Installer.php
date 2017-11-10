<?php
include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

class WP_MadeIT_Security_Plugin_Installer extends Plugin_Upgrader
{
    /**
     * Upgrade a plugin with packaged provided.
     *
     *
     * @param string $plugin  The basename path to the main plugin file.
     * @param string $package The full local path or URI of the package.
     * @param array  $args    {
     *                        Optional. Other arguments for upgrading a plugin package. Default empty array.
     *
     *     @var bool $clear_update_cache Whether to clear the plugin updates cache if successful.
     *                                    Default true.
     * }
     *
     * @return bool|WP_Error True if the upgrade was successful, false or a WP_Error object otherwise.
     */
    public function upgradeWithPackage($plugin, $package = null, $args = [])
    {
        $defaults = [
            'clear_update_cache' => true,
        ];
        $parsed_args = wp_parse_args($args, $defaults);
        $this->init();
        $this->upgrade_strings();

        // Get the URL to the zip file
        if($package == null) {
            $current = get_site_transient('update_plugins');
            $package = $current->response[$plugin];
        }
        add_filter('upgrader_pre_install', [$this, 'deactivate_plugin_before_upgrade'], 10, 2);
        add_filter('upgrader_clear_destination', [$this, 'delete_old_plugin'], 10, 4);
        //'source_selection' => array($this, 'source_selection'), //there's a trac ticket to move up the directory for zip's which are made a bit differently, useful for non-.org plugins.
        if ($parsed_args['clear_update_cache']) {
            // Clear cache so wp_update_plugins() knows about the new plugin.
            add_action('upgrader_process_complete', 'wp_clean_plugins_cache', 9, 0);
        }
        $this->run([
            'package'           => $package,
            'destination'       => WP_PLUGIN_DIR,
            'clear_destination' => true,
            'clear_working'     => true,
            'hook_extra'        => [
                'plugin' => $plugin,
                'type'   => 'plugin',
                'action' => 'update',
            ],
        ]);
        // Cleanup our hooks, in case something else does a upgrade on this connection.
        remove_action('upgrader_process_complete', 'wp_clean_plugins_cache', 9);
        remove_filter('upgrader_pre_install', [$this, 'deactivate_plugin_before_upgrade']);
        remove_filter('upgrader_clear_destination', [$this, 'delete_old_plugin']);
        if (!$this->result || is_wp_error($this->result)) {
            return $this->result;
        }
        // Force refresh of plugin update information
        wp_clean_plugins_cache($parsed_args['clear_update_cache']);

        return true;
    }
}
