<?php

class WP_MadeIT_Security_Core {
	public function hasUpdate() {
		return $this->getCurrentWPVersion() != $this->getLatestWPVersion();
	}
	
    public function getCurrentWPVersion() {
        // include an unmodified $wp_version
        include(ABSPATH . WPINC . '/version.php');
        return $wp_version;
    }
    
    public function getLatestWPVersion() {
        $core = $this->wp_version_check();
        if(isset($core['offers'][0]['current'])) {
            $latest_core_version = $core['offers'][0]['current'];
        }
        else {
            $latest_core_version = null;
        }
        return $latest_core_version;
    }
    
    private function wp_version_check() {
        if (wp_installing()) {
            return;
        }
        global $wpdb, $wp_local_package;
        // include an unmodified $wp_version
        include(ABSPATH . WPINC . '/version.php');
        $php_version = phpversion();
        
	    $translations = wp_get_installed_translations( 'core' );

        $locale = apply_filters( 'core_version_check_locale', get_locale() );

        if ( method_exists( $wpdb, 'db_version' ) )
            $mysql_version = preg_replace('/[^0-9.].*/', '', $wpdb->db_version());
        else
            $mysql_version = 'N/A';

        if ( is_multisite() ) {
            $user_count = get_user_count();
            $num_blogs = get_blog_count();
            $wp_install = network_site_url();
            $multisite_enabled = 1;
        } else {
            $user_count = count_users();
            $user_count = $user_count['total_users'];
            $multisite_enabled = 0;
            $num_blogs = 1;
            $wp_install = home_url( '/' );
        }
        $query = array(
            'version'            => $wp_version,
            'php'                => $php_version,
            'locale'             => $locale,
            'mysql'              => $mysql_version,
            'local_package'      => isset( $wp_local_package ) ? $wp_local_package : '',
            'blogs'              => $num_blogs,
            'users'              => $user_count,
            'multisite_enabled'  => $multisite_enabled,
            'initial_db_version' => get_site_option( 'initial_db_version' ),
        );
        $post_body = array(
            'translations' => wp_json_encode( $translations ),
        );
        $url = $http_url = 'http://api.wordpress.org/core/version-check/1.7/?' . http_build_query( $query, null, '&' );
        if ( $ssl = wp_http_supports( array( 'ssl' ) ) )
            $url = set_url_scheme( $url, 'https' );
        $options = array(
            'timeout' => 3,
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
            'headers' => array(
                'wp_install' => $wp_install,
                'wp_blog' => home_url( '/' )
            ),
            'body' => $post_body,
        );
        $response = wp_remote_post( $url, $options );
        if ( $ssl && is_wp_error( $response ) ) {
            $response = wp_remote_post( $http_url, $options );
        }
        if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
            return;
        }
        $body = trim( wp_remote_retrieve_body( $response ) );
        $body = json_decode( $body, true );
		if ( ! is_array( $body ) || ! isset( $body['offers'] ) ) {
			return;
		}
		$offers = $body['offers'];
		foreach ( $offers as &$offer ) {
			foreach ( $offer as $offer_key => $value ) {
				if ( 'packages' == $offer_key )
					$offer['packages'] = (object) array_intersect_key( array_map( 'esc_url', $offer['packages'] ),
						array_fill_keys( array( 'full', 'no_content', 'new_bundled', 'partial', 'rollback' ), '' ) );
				elseif ( 'download' == $offer_key )
					$offer['download'] = esc_url( $value );
				else
					$offer[ $offer_key ] = esc_html( $value );
			}
			$offer = (object) array_intersect_key( $offer, array_fill_keys( array( 'response', 'download', 'locale',
				'packages', 'current', 'version', 'php_version', 'mysql_version', 'new_bundled', 'partial_version', 'notify_email', 'support_email', 'new_files' ), '' ) );
		}
		$updates = new stdClass();
		$updates->updates = $offers;
		$updates->last_checked = time();
		$updates->version_checked = $wp_version;
		if ( isset( $body['translations'] ) )
			$updates->translations = $body['translations'];
		set_site_transient( 'update_core', $updates );
        return $body;
    }
}