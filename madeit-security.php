<?php
/**
 * Plugin Name: WP Security by Made I.T.
 * Plugin URI: https://www.madeit.be/wordpress-onderhoud
 * Description: Secure your WordPress Website.
 * Author: Made I.T.
 * Author URI: https://www.madeit.be
 * Version: 1.5.1
 * Text Domain: wp-security-by-made-it
 * Domain Path: /languages
 * License: GPLv3.
 */
if (!defined('ABSPATH')) {
    die('No direct access allowed');
}

// Defines
if (!defined('MADEIT_SECURITY_DIR')) {
    define('MADEIT_SECURITY_DIR', dirname(__FILE__)); // Plugin Dir
}
if (!defined('MADEIT_SECURITY_URL')) {
    define('MADEIT_SECURITY_URL', plugin_dir_url(__FILE__)); // Plugin URL
}
if (!defined('MADEIT_SECURITY_ADMIN')) {
    define('MADEIT_SECURITY_ADMIN', MADEIT_SECURITY_DIR.'/admin'); // Admin Dir
}
if (!defined('MADEIT_SECURITY_FRONT')) {
    define('MADEIT_SECURITY_FRONT', MADEIT_SECURITY_DIR.'/front'); // Admin Dir
}
if (!defined('MADEIT_SECURITY_API')) {
    define('MADEIT_SECURITY_API', false); // Is API active
}
if (!defined('MADEIT_SECURITY_SUBDIRECTORY_INSTALL')) {
    define('MADEIT_SECURITY_SUBDIRECTORY_INSTALL', class_exists('WP_MadeIT_Security_Init') && !in_array(realpath(dirname(__FILE__) . '/inc/firewall/WP_MadeIT_Security_Init.php'), get_included_files()));
}

function wp_security_by_madeit_load_plugin_textdomain()
{
    load_plugin_textdomain('wp-security-by-made-it', false, basename(dirname(__FILE__)).'/languages/');
}
add_action('plugins_loaded', 'wp_security_by_madeit_load_plugin_textdomain');

require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_DB.php';
$wp_madeit_security_db = new WP_MadeIT_Security_DB();

require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Settings.php';
$wp_madeit_security_settings = new WP_MadeIT_Security_Settings();

if (defined('DOING_CRON')) {
    $settings = $wp_madeit_security_settings->loadDefaultSettings();
    $scan = $settings['scan']['repo']['core'] && $settings['scan']['repo']['theme'] && $settings['scan']['repo']['plugin'];
    if ($scan) {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_LoadFiles.php';
        $wp_madeit_security_loadfiles = new WP_MadeIT_Security_LoadFiles($wp_madeit_security_settings, $wp_madeit_security_db);
        $wp_madeit_security_loadfiles->addHooks();
    }

    if ($settings['maintenance']['backup'] || $settings['backup']['ftp']['enabled'] || $settings['backup']['s3']['enabled']) {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup.php';
        $wp_madeit_security_backup = new WP_MadeIT_Security_Backup($wp_madeit_security_settings, $wp_madeit_security_db);
        $wp_madeit_security_backup->addHooks();
    }
} else {
    require_once MADEIT_SECURITY_DIR.'/admin/WP_MadeIT_Security_Admin.php';
    $wp_madeit_security_admin = new WP_MadeIT_Security_Admin($wp_madeit_security_settings, $wp_madeit_security_db);
    $wp_madeit_security_admin->addHooks();
}

require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Update.php';
$wp_madeit_security_plugin = new WP_MadeIT_Security_Update($wp_madeit_security_settings, $wp_madeit_security_db);
$wp_madeit_security_plugin->addHooks();
