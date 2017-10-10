<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/*
 * Made I.T.
 *
 * @package Made I.T.
 * @since 1.0.0
 */
?>
<div class="wrap">
    <h1><?php echo esc_html(__('Settings', 'wp-security-by-made-it')); ?></h1>
    <form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" id="madeit-security-admin-form-element">
        <?php if ($success) {
    ?>
            <div class="updated"><p><strong><?php echo __('The settings are successfully saved.', 'wp-security-by-made-it'); ?></strong></p></div>
            <?php
}
        if (!empty($error)) {
            ?>
            <div class="error"><p><strong><?php echo __($error, 'wp-security-by-made-it'); ?></strong></p></div>
            <?php
        }
        ?>
        <input type="hidden" name="save_settings" value="Y">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="informationdiv" class="postbox">
                        <h3><?php echo esc_html(__('Information', 'wp-security-by-made-it')); ?></h3>
                        <div class="inside">
                            <ul>
                                <li><?php echo sprintf('<a href="%1$s"%3$s" title="%2$s">%2$s</a>', esc_url('https://madeit.be/wordpress-onderhoud/wp-plugin/#docs'), __('Docs', 'wp-security-by-made-it'), ''); ?></li>
                                <li><?php echo sprintf('<a href="%1$s"%3$s" title="%2$s">%2$s</a>', esc_url('https://madeit.be/wordpress-onderhoud/wp-plugin/#faq'), __('F.A.Q.', 'wp-security-by-made-it'), ''); ?></li>
                                <li><?php echo sprintf('<a href="%1$s"%3$s" title="%2$s">%2$s</a>', esc_url('https://madeit.be/wordpress-onderhoud/wp-plugin/#support'), __('Support', 'wp-security-by-made-it'), ''); ?></li>
                            </ul>
                        </div>
                    </div><!-- #informationdiv -->
                </div><!-- #postbox-container-1 -->

                <div id="postbox-container-2" class="postbox-container">
                    <div id="madeit-tab">
                        <ul id="madeit-tab-tabs">
                            <li id="general-settings-tab"><a href="#general-settings"><?php echo esc_html(__('General settings', 'wp-security-by-made-it')); ?></a></li>
                            <li id="backup-tab"><a href="#backup-settings"><?php echo esc_html(__('Back-up settings', 'wp-security-by-made-it')); ?></a></li>
                            <li id="maintenance-settings-tab"><a href="#maintenance-panel"><?php echo esc_html(__('Maintenance', 'wp-security-by-made-it')); ?></a></li>
                        </ul>
                        <div class="madeit-tab-panel" id="general-settings">
                            <section class="section">
                                <h3><?php echo esc_html(__('Scan files against repository versions for changes', 'wp-security-by-made-it')); ?></h3>
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Do fast scan', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_scan_repo_fast" class="" value="1" <?php if ($this->defaultSettings['scan']['fast']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Scan core files', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_scan_repo_core" class="" value="1" <?php if ($this->defaultSettings['scan']['repo']['core']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Scan theme files', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_scan_repo_theme" class="" value="1" <?php if ($this->defaultSettings['scan']['repo']['theme']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Scan plugin files', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_scan_repo_plugin" class="" value="1" <?php if ($this->defaultSettings['scan']['repo']['plugin']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </section>
                            <section class="section">
                                <h3><?php echo esc_html(__('Updates', 'wp-security-by-made-it')); ?></h3>
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Scan updates', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_scan_update" class="" value="1" <?php if ($this->defaultSettings['scan']['update']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </section>
                            <section class="section">
                                <h3><?php echo esc_html(__('API Integration', 'wp-security-by-made-it')); ?></h3>
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Made I.T. API Key', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="madeit_security_api_key" class="large-text code" size="70" value="<?php echo $this->defaultSettings['api']['key']; ?>" />
                                                <p>
                                                    <?php echo esc_html(__('This API key is auto generated. The API key is required to communicate with our server to update file scan listing, Virus/Mallware databases, ...', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </section>
                        </div>
                        <div class="madeit-tab-panel" id="backup-settings">
                            <section class="section">
                                <h3><?php echo esc_html(__('Send the backup to your FTP server.', 'wp-security-by-made-it')); ?></h3>
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Enable FTP Backup', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_backup_ftp_enable" class="" value="1" <?php if ($this->defaultSettings['backup']['ftp']['enabled']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('FTP server', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="madeit_security_backup_ftp_server" class="large-text code" size="70" value="<?php echo $this->defaultSettings['backup']['ftp']['server']; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('FTP username', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="madeit_security_backup_ftp_username" class="large-text code" size="70" value="<?php echo $this->defaultSettings['backup']['ftp']['username']; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('FTP password', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="password" name="madeit_security_backup_ftp_password" class="large-text code" size="70" value="<?php echo $this->defaultSettings['backup']['ftp']['password']; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('FTP destination directory', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="madeit_security_backup_ftp_destination_directory" class="large-text code" size="70" value="<?php echo $this->defaultSettings['backup']['ftp']['destination_dir']; ?>" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </section>
                            <section class="section">
                                <h3><?php echo esc_html(__('Send the backup to your S3 bucket.', 'wp-security-by-made-it')); ?></h3>
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Enable S3 Backup', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_backup_s3_enable" class="" value="1" <?php if ($this->defaultSettings['backup']['s3']['enabled']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Access key', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="madeit_security_backup_s3_access_key" class="large-text code" size="70" value="<?php echo $this->defaultSettings['backup']['s3']['access_key']; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Secret key', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="madeit_security_backup_s3_secret_key" class="large-text code" size="70" value="<?php echo $this->defaultSettings['backup']['s3']['secret_key']; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Bucket', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="madeit_security_backup_s3_bucket_name" class="large-text code" size="70" value="<?php echo $this->defaultSettings['backup']['s3']['bucket_name']; ?>" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </section>
                        </div>
                        <div class="madeit-tab-panel" id="maintenance-panel">
                            <h2><?php echo esc_html(__('Maintenance', 'wp-security-by-made-it')); ?></h2>
                            <section class="section">
                                <h3><?php echo esc_html(__('Prevent your website from hacking', 'wp-security-by-made-it')); ?></h3>
                                <p>
                                   <?php echo esc_html(__(' Prevent your website from hacking. Made I.T. can take care of your WordPress website to take the control of the updates and optimizations.', 'wp-security-by-made-it')); ?> 
                                </p>
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Let Made I.T. Take over the maintenance', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_maintenance_enable" class="" value="1" <?php if ($this->defaultSettings['maintenance']['enable']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Backup your website', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="checkbox" name="madeit_security_maintenance_backup" class="" value="1" <?php if ($this->defaultSettings['maintenance']['backup']) {
            echo 'CHECKED';
        } ?> />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </section>
                            <section class="section">
                                <h3><?php echo esc_html(__('API Integration', 'wp-security-by-made-it')); ?></h3>
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for=""><?php echo esc_html(__('Made I.T. WordPress Maintenance API Key', 'wp-security-by-made-it')); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="madeit_security_maintenance_api_key" class="large-text code" size="70" value="<?php echo $this->defaultSettings['maintenance']['key']; ?>" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </section>
                        </div>
                    </div><!-- #madeit-tab -->
                    <p class="submit">
                        <?php
                        $nonce = wp_create_nonce('madeit_security_settings');
                        ?>
                        <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
                        <input type="submit" class="button-primary" value="<?php echo esc_html(__('Save', 'wp-security-by-made-it')); ?>" />
                    </p>
                </div><!-- #postbox-container-2 -->
            </div><!-- #post-body -->
            <br class="clear" />
        </div><!-- #poststuff -->
    </form>
</div><!-- .wrap -->
<?php
/*add_thickbox();*/
