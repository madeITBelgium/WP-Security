<?php

class WP_MadeIT_Security_Theme_Installer extends Theme_Upgrader {
	
	/**
	 * Upgrade a theme.
	 *
	 * @since 2.8.0
	 * @since 3.7.0 The `$args` parameter was added, making clearing the update cache optional.
	 *
	 * @param string $theme The theme slug.
	 * @param string $package The full local path or URI of the package.
	 * @param array  $args {
	 *     Optional. Other arguments for upgrading a theme. Default empty array.
	 *
	 *     @type bool $clear_update_cache Whether to clear the update cache if successful.
	 *                                    Default true.
	 * }
	 * @return bool|WP_Error True if the upgrade was successful, false or a WP_Error object otherwise.
	 */
	public function upgradeWithPackage( $theme, $package, $args = array() ) {
		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = wp_parse_args( $args, $defaults );
		$this->init();
		$this->upgrade_strings();
		
		add_filter('upgrader_pre_install', array($this, 'current_before'), 10, 2);
		add_filter('upgrader_post_install', array($this, 'current_after'), 10, 2);
		add_filter('upgrader_clear_destination', array($this, 'delete_old_theme'), 10, 4);
		if ( $parsed_args['clear_update_cache'] ) {
			// Clear cache so wp_update_themes() knows about the new theme.
			add_action( 'upgrader_process_complete', 'wp_clean_themes_cache', 9, 0 );
		}
		$this->run(array(
			'package' => $package,
			'destination' => get_theme_root( $theme ),
			'clear_destination' => true,
			'clear_working' => true,
			'hook_extra' => array(
				'theme' => $theme,
				'type' => 'theme',
				'action' => 'update',
			),
		));
		remove_action('upgrader_process_complete', 'wp_clean_themes_cache', 9);
		remove_filter('upgrader_pre_install', array($this, 'current_before'));
		remove_filter('upgrader_post_install', array($this, 'current_after'));
		remove_filter('upgrader_clear_destination', array($this, 'delete_old_theme'));
		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;
		wp_clean_themes_cache( $parsed_args['clear_update_cache'] );
		return true;
	}
}