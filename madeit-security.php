<?php
/**
 * Plugin Name: WP Security by Made I.T.
 * Plugin URI: https://www.madeit.be/wordpress-onderhoud
 * Description: Secure your WordPress Website.
 * Author: Made I.T.
 * Author URI: https://www.madeit.be
 * Version: 1.0.1
 * Text Domain: madeit_security
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
    define('MADEIT_SECURITY_API', false); // Admin Dir
}

require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Settings.php';
$wp_madeit_security_settings = new WP_MadeIT_Security_Settings();

require_once MADEIT_SECURITY_DIR.'/admin/WP_MadeIT_Security_Admin.php';
$wp_madeit_security_admin = new WP_MadeIT_Security_Admin($wp_madeit_security_settings);
$wp_madeit_security_admin->addHooks();

require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Update.php';
$wp_madeit_security_plugin = new WP_MadeIT_Security_Update($wp_madeit_security_settings);
$wp_madeit_security_plugin->addHooks();

$settings = $wp_madeit_security_settings->loadDefaultSettings();
$scan = $settings['scan']['repo']['core'] && $settings['scan']['repo']['theme'] && $settings['scan']['repo']['plugin'];
if ($scan) {
    require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Scan.php';
    $wp_madeit_security_scan = new WP_MadeIT_Security_Scan();
    $wp_madeit_security_scan->addHooks($wp_madeit_security_settings);
}

if ($settings['maintenance']['backup']) {
    require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup.php';
    $wp_madeit_security_backup = new WP_MadeIT_Security_Backup($wp_madeit_security_settings);
    $wp_madeit_security_backup->addHooks();
}
