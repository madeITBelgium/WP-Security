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
        $this->assertSame(MADEIT_SECURITY_DIR, $path);
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
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Render/Abstract.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Render/Html/Array.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Render/Html/Inline.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Render/Html/SideBySide.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Render/Text/Context.php');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/inc/compare/Diff/Render/Text/Unified.php');
        /* Check Assets Exist */
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/assets/icon-128x128.png');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/assets/icon-16x16.png');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/assets/icon-256x256.png');
        $this->assertFileExists(MADEIT_SECURITY_DIR.'/assets/icon-64x64.png');
    }
}
