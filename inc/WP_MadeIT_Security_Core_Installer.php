<?php
class WP_MadeIT_Security_Core_Installer extends Core_Upgrader {

    /**
     * Upgrade WordPress core.
     *
     * @global WP_Filesystem_Base $wp_filesystem Subclass
     * @global callable           $_wp_filesystem_direct_method
     *
     * @param string $package The full local path or URI of the package.
     *
     * @param string $rollbackPackage The full local path or URI of the rollback package.
     *
     * @param array  $args {
     *        Optional. Arguments for upgrading WordPress core. Default empty array.
     *
     *        @type bool $pre_check_md5    Whether to check the file checksums before
     *                                     attempting the upgrade. Default true.
     *        @type bool $attempt_rollback Whether to attempt to rollback the chances if
     *                                     there is a problem. Default false.
     *        @type bool $do_rollback      Whether to perform this "upgrade" as a rollback.
     *                                     Default false.
     * }
     * @return null|false|WP_Error False or WP_Error on failure, null on success.
     */
    public function upgradeWithPackage($package, $rollbackPackage, $args = array() ) {
        global $wp_filesystem;
        include( ABSPATH . WPINC . '/version.php' ); // $wp_version;
        $start_time = time();
        $defaults = array(
            'pre_check_md5'    => true,
            'attempt_rollback' => false,
            'do_rollback'      => false,
            'allow_relaxed_file_ownership' => false,
        );
        $parsed_args = wp_parse_args( $args, $defaults );
        $this->init();
        $this->upgrade_strings();

        $res = $this->fs_connect( array( ABSPATH, WP_CONTENT_DIR ), $parsed_args['allow_relaxed_file_ownership'] );
        if ( ! $res || is_wp_error( $res ) ) {
            return $res;
        }
        $wp_dir = trailingslashit($wp_filesystem->abspath());

        // Lock to prevent multiple Core Updates occurring
        $lock = WP_Upgrader::create_lock( 'core_updater', 15 * MINUTE_IN_SECONDS );
        if ( ! $lock ) {
            return new WP_Error( 'locked', $this->strings['locked'] );
        }
        $download = $this->download_package( $package );
        if ( is_wp_error( $download ) ) {
            WP_Upgrader::release_lock( 'core_updater' );
            return $download;
        }
        $working_dir = $this->unpack_package( $download );
        if ( is_wp_error( $working_dir ) ) {
            WP_Upgrader::release_lock( 'core_updater' );
            return $working_dir;
        }
        // Copy update-core.php from the new version into place.
        if ( !$wp_filesystem->copy($working_dir . '/wordpress/wp-admin/includes/update-core.php', $wp_dir . 'wp-admin/includes/update-core.php', true) ) {
            $wp_filesystem->delete($working_dir, true);
            WP_Upgrader::release_lock( 'core_updater' );
            return new WP_Error( 'copy_failed_for_update_core_file', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 'wp-admin/includes/update-core.php' );
        }
        $wp_filesystem->chmod($wp_dir . 'wp-admin/includes/update-core.php', FS_CHMOD_FILE);
        require_once( ABSPATH . 'wp-admin/includes/update-core.php' );
        if ( ! function_exists( 'update_core' ) ) {
            WP_Upgrader::release_lock( 'core_updater' );
            return new WP_Error( 'copy_failed_space', $this->strings['copy_failed_space'] );
        }
        $result = update_core( $working_dir, $wp_dir );
        // In the event of an issue, we may be able to roll back.
        if ( $parsed_args['attempt_rollback'] && $rollbackPackage && ! $parsed_args['do_rollback'] ) {
            $try_rollback = false;
            if ( is_wp_error( $result ) ) {
                $error_code = $result->get_error_code();
                /*
                 * Not all errors are equal. These codes are critical: copy_failed__copy_dir,
                 * mkdir_failed__copy_dir, copy_failed__copy_dir_retry, and disk_full.
                 * do_rollback allows for update_core() to trigger a rollback if needed.
                 */
                if ( false !== strpos( $error_code, 'do_rollback' ) )
                    $try_rollback = true;
                elseif ( false !== strpos( $error_code, '__copy_dir' ) )
                    $try_rollback = true;
                elseif ( 'disk_full' === $error_code )
                    $try_rollback = true;
            }
            if ( $try_rollback ) {
                /** This filter is documented in wp-admin/includes/update-core.php */
                apply_filters( 'update_feedback', $result );
                /** This filter is documented in wp-admin/includes/update-core.php */
                apply_filters( 'update_feedback', $this->strings['start_rollback'] );
                $rollback_result = $this->upgradeWithPackage( $rollbackPackage, null, array_merge( $parsed_args, array( 'do_rollback' => true ) ) );
                $original_result = $result;
                $result = new WP_Error( 'rollback_was_required', $this->strings['rollback_was_required'], (object) array( 'update' => $original_result, 'rollback' => $rollback_result ) );
            }
        }
        /** This action is documented in wp-admin/includes/class-wp-upgrader.php */
        do_action( 'upgrader_process_complete', $this, array( 'action' => 'update', 'type' => 'core' ) );
        // Clear the current updates
        delete_site_transient( 'update_core' );

        WP_Upgrader::release_lock( 'core_updater' );
        return $result;
    }
}