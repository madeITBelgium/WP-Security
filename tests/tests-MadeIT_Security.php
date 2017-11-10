<?php

class Tests_WP_MadeIT_Security extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_constants()
    {
        // Plugin Folder URL
        $path = str_replace('tests/', '', plugin_dir_url(__FILE__));
        $this->assertSame(MADEIT_SECURITY_URL, $path);
        // Plugin Folder Path
        $path = str_replace('tests/', '', plugin_dir_path(__FILE__));
        $this->assertSame(MADEIT_SECURITY_DIR, substr($path, 0, -1));
    }

    public function test_includes()
    {
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/admin/WP_MadeIT_Security_Admin.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup_Database.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Backup_Files.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core_Installer.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core_Scan.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_DB.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_DB_Schema.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_LoadFiles.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Maintenance.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin_Installer.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin_Scan.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Scan.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Settings.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_SystemInfo.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme_Installer.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme_Scan.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Update.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/SequenceMatcher.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Renderer/Abstract.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Renderer/Html/Array.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Renderer/Html/Inline.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Renderer/Html/SideBySide.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Renderer/Text/Context.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Renderer/Text/Unified.php');
        /* Check Assets Exist */
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/assets/icon-128x128.png');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/assets/icon-16x16.png');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/assets/icon-256x256.png');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/assets/icon-64x64.png');
    }

    public function test_dashboard()
    {
        wp_set_current_user(1);
        $this->go_to('/wp-admin/admin.php?page=madeit_security');
    }

    public function test_scan()
    {
        wp_set_current_user(1);
        $this->go_to('/wp-admin/admin.php?page=madeit_security_scan');
    }

    public function test_settings()
    {
        wp_set_current_user(1);
        $this->go_to('/wp-admin/admin.php?page=madeit_security_settings');
    }

    public function test_systeminfo()
    {
        wp_set_current_user(1);
        $this->go_to('/wp-admin/admin.php?page=madeit_security_systeminfo');
    }

    public function test_actions()
    {
        $actions = ['wp_ajax_madeit_security_start_scan',  'wp_ajax_madeit_security_stop_scan', 'wp_ajax_madeit_security_update_scan', 'wp_ajax_madeit_security_backup', 'wp_ajax_madeit_security_backup_check', 'wp_ajax_madeit_security_backup_stop', 'wp_ajax_madeit_security_check_scan', 'wp_ajax_madeit_security_do_update'];
        foreach ($actions as $action) {
            $this->assertTrue(has_action($action));
        }
    }
}
