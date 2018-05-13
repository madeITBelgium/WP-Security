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
    <?php if (isset($successUninstall) && $successUninstall === true) {
    ?>
        <div class="updated"><p><strong><?php echo __('The firewall is succesfully disabled.', 'wp-security-by-made-it'); ?></strong></p></div>
        <?php
} elseif (isset($successUninstall) && $successUninstall === false) {
        $url = esc_url(add_query_arg([
            'action'              => 'uninstallFirewall',
            'firewallnonce'       => $nonceUninstall,
            'remove_ini'          => 1,
        ], $adminURL)); ?>
        <div class="updated"><p><strong><?php printf(__('The firewall is succesfully disabled. To remove the firewall files please click here: <a href="%s">Finish firewall uninstall</a>.', 'wp-security-by-made-it'), $url); ?></strong></p></div>
        <?php
    }
    if (isset($successInstall)) {
        ?>
        <div class="updated"><p><strong><?php echo __('The firewall is succesfully installed.', 'wp-security-by-made-it'); ?></strong></p></div>
        <?php
    }
    if (!empty($error)) {
        ?>
        <div class="error"><p><strong><?php echo esc_html($error); ?></strong></p></div>
        <?php
    }
    ?>
    
    <div class="madeit-container-fluid">
        <div class="madeit-row">
            <div class="madeit-col">
                <h1><?php echo esc_html(__('Firewall', 'wp-security-by-made-it')); ?></h1>
            </div>
        </div>
        
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <!-- Firewall summary -->
                <div class="madeit-row">
                    <div class="madeit-col">
                        <div class="madeit-card">
                            <div class="madeit-card-body">
                                <h4 class="madeit-card-title">
                                    <?php echo esc_html(__('Firewall summary', 'wp-security-by-made-it')); ?>
                                </h4>
                                <h6 class="madeit-card-subtitle" id="firewall-time-ago">
                                    <?php echo esc_html(__('No recent data found.', 'wp-security-by-made-it')); ?>
                                </h6>
                                <?php if ($this->defaultSettings['firewall']['enabled']) {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col madeit-text-center">
                                                <p class="madeit-card-title" id="repo-scan-core-status">
                                                    <?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('WordPress Core', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col  madeit-text-center">
                                                <p class="madeit-card-title" id="repo-scan-plugins-status">
                                                    <?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Plugins', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col madeit-text-center">
                                                <p class="madeit-card-title" id="repo-scan-themes-status">
                                                   <?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Themes', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php
    } else {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Firewall is disabled.', 'wp-security-by-made-it')); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
    } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Firewall summary -->
            </div>
            <div class="madeit-col">
                
                <!-- Firewall stats -->
                <div class="madeit-row">
                    <div class="madeit-col">
                        <div class="madeit-card">
                            <div class="madeit-card-body">
                                <h4 class="madeit-card-title">
                                    <?php echo esc_html(__('Firewall stats', 'wp-security-by-made-it')); ?>
                                </h4>
                                <?php if ($this->defaultSettings['firewall']['enabled']) {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col  madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-core-status">
                                                    <?php if (isset($updateScanData['core'])) {
            echo esc_html($updateScanData['core']);
        } else {
            echo esc_html(__('N/A', 'wp-security-by-made-it'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Blocked', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col  madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-plugins-status">
                                                    <?php if (isset($updateScanData['plugin'])) {
            echo esc_html($updateScanData['plugin']);
        } else {
            echo esc_html(__('N/A', 'wp-security-by-made-it'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Brute Force', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-themes-status">
                                                   <?php if (isset($updateScanData['theme'])) {
            echo esc_html($updateScanData['theme']);
        } else {
            echo esc_html(__('N/A', 'wp-security-by-made-it'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Made I.T. Network', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php
    } else {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Firewall disabled.', 'wp-security-by-made-it')); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
    } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Firewall stats -->
            </div>
        </div>
        <?php if ($this->defaultSettings['firewall']['enabled']) {
        ?>
            <div class="madeit-row" style="margin-top: 20px;">
                <div class="madeit-col">
                    <div class="madeit-card">
                        <div class="madeit-card-body">
                            <h4 class="madeit-card-title">
                                <?php echo esc_html(__('Scan result', 'wp-security-by-made-it')); ?>
                                <small>
                                    <h6 style="display:inline;">
                                        <?php echo sprintf(_n('%s issue found', '%s issues found', 0, 'wp-security-by-made-it'), 0); ?>
                                    </h6>
                                </small>
                            </h4>
                            <div class="card-text">
                                <div class="madeit-row">
                                    <div class="card-text" style="margin-top: 20px; margin-bottom: 20px; width: 100%">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php
                                                $url = esc_url(add_query_arg([
                                                    'action'              => 'uninstallFirewall',
                                                    'firewallnonce'       => $nonceUninstall,
                                                    'remove_ini'          => 1,
                                                ], $adminURL)); ?>
                                                <a href="<?php echo $url; ?>"><?php _e('Disable the firewall', 'wp-security-by-made-it'); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    } else {
        ?>
            <div class="madeit-row" style="margin-top: 20px;">
                <div class="madeit-col">
                    <div class="madeit-card">
                        <div class="madeit-card-body">
                            <h4 class="madeit-card-title">
                                <?php echo esc_html(__('Setup Firewall', 'wp-security-by-made-it')); ?>
                            </h4>
                            <div class="card-text">
                                <div class="madeit-row">
                                    <div class="card-text" style="margin-top: 20px; margin-bottom: 20px; margin-left: 15px; margin-right: 15px; width: 100%">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php
                                                if (empty($currentAutoPrependFile)) {
                                                    ?>
                                                    <p><?php _e('To make your site as secure as possible, the Firewall is designed to run via a PHP setting called <code>auto_prepend_file</code>, which ensures it runs before any potentially vulnerable code runs.', 'wp-security-by-made-it'); ?></p>
                                                <?php
                                                } else {
                                                    ?>
                                                    <p><?php _e('To make your site as secure as possible, the Firewall is designed to run via a PHP setting called <code>auto_prepend_file</code>, which ensures it runs before any potentially vulnerable code runs. This PHP setting is currently in use, and is including this file:', 'wp-security-by-made-it'); ?></p>
                                                    <pre><code><?php echo esc_html($currentAutoPrependFile); ?></code></pre>
                                                    <p><?php _e('If you don\'t recognize this file, please <a href="https://wordpress.org/support/plugin/wp-security-by-made-it" target="_blank" rel="noopener noreferrer">contact us on the WordPress support forums</a> before proceeding.', 'wp-security-by-made-it'); ?></p>
                                                    <p><?php _e('You can proceed with the installation and we will include this from within our <code>security-firewall.php</code> file which should maintain compatibility with your site.', 'wp-security-by-made-it'); ?></p>
                                                <?php
                                                } ?>
                                                <div class="madeit-alert-warning"><strong><?php _e('NOTE:', 'wp-security-by-made-it'); ?></strong> <?php _e('If you have separate WordPress installations with WP Security installed within a subdirectory of this site, it is recommended that you perform the Firewall installation procedure on those sites before this one.', 'wp-security-by-made-it'); ?></div>
                                                <?php
                                                if (!$optionFound) {
                                                    ?>
                                                    <p><?php _e('We couldn\'t detect the web server\'s configuration.', 'wp-security-by-made-it'); ?></p>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <p><?php printf(__('We\'ve preselected your server configuration based on our tests.: %s', 'wp-security-by-made-it'), $optionName); ?></p>
                                                    <?php if ($option == 'nginx') {
                                                        ?>
                                                        <div class="madeit-alert-warning"><?php printf(__('Part of the Firewall configuration procedure for NGINX depends on creating a <code>%s</code> file in the root of your WordPress installation. This file can contain sensitive information and public access to it should be restricted. We have <a href="%s">instructions on our documentation site</a> on what directives to put in your nginx.conf to fix this.', 'wp-security-by-made-it'), esc_html(ini_get('user_ini.filename')), 'https://www.madeit.be'); ?></div>
                                                    <?php
                                                    } ?>
                                                
                                                    <p><?php _e('Please download a backup of the following files before we make the necessary changes:', 'wp-security-by-made-it'); ?></p>
                                                    <ul class="madeit-firewall-backup-files">
                                                        <?php
                                                        foreach ($backupFiles as $index => $backup) {
                                                            echo '<li><a class="madeit-btn madeit-btn-outline-primary" data-backup-index="'.$index.'" href="'.
                                                            esc_url(add_query_arg([
                                                                'action'              => 'downloadConfigBackup',
                                                                'downloadBackup'      => 1,
                                                                'backupIndex'         => $index,
                                                                'serverConfiguration' => $option,
                                                                'firewallnonce'       => $nonceInstall,
                                                                ], $adminURL)).'">'.sprintf(__('Download %s', 'wp-security-by-made-it'), esc_html(basename($backup))).'</a></li>';
                                                        } ?>
                                                    </ul><br>
                                                    <a class="madeit-btn madeit-btn-outline-primary install-firewall" style="display: none;" href="<?php echo esc_url(add_query_arg([
                                                                'action'              => 'installFirewall',
                                                                'serverConfiguration' => $option,
                                                                'firewallnonce'       => $nonceInstall,
                                                                ], $adminURL)); ?>"><?php _e('Enable Firewall', 'wp-security-by-made-it'); ?></a>
                                            <?php
                                                } ?>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    } ?>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        var i = 0;
        $('.madeit-firewall-backup-files a').click(function(e) {
            i++;
            if(i == $('.madeit-firewall-backup-files a').length) {
                $('.install-firewall').show();
            }
        });
    });
</script>