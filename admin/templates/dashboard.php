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
    <div class="madeit-container-fluid">
        <div class="madeit-row">
            <div class="madeit-col">
                <h1><?php echo esc_html(__('Security Dashboard', 'madeit_security')); ?></h1>
            </div>
        </div>
        
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('Feature status', 'madeit_security')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <?php foreach ([
                                     __('Scan repo changes', 'madeit_security') => wp_next_scheduled('madeit_security_scan_repo') ? 'OK' : 'NOK',
                                    __('Fast scan', 'madeit_security') => $this->defaultSettings['scan']['fast'] ? 'OK' : 'NOK',
                                     __('Scan core changes', 'madeit_security') => $this->defaultSettings['scan']['repo']['core'] ? 'OK' : 'NOK',
                                     __('Scan plugin changes', 'madeit_security') => $this->defaultSettings['scan']['repo']['plugin'] ? 'OK' : 'NOK',
                                     __('Scan theme changes', 'madeit_security') => $this->defaultSettings['scan']['repo']['theme'] ? 'OK' : 'NOK',
                                     __('Scan updates', 'madeit_security') => $this->defaultSettings['scan']['update'] ? 'OK' : 'NOK',
                                     __('Maintenance by Made I.T.', 'madeit_security') => $this->defaultSettings['maintenance']['enable'] ? 'OK' : 'NOK',
                                     __('Scan plugin updates', 'madeit_security') => wp_next_scheduled('madeit_security_check_plugin_updates') ? 'OK' : 'NOK',
                                     __('Backup', 'madeit_security') => wp_next_scheduled('madeit_security_backup') ? 'OK' : 'NOK',
                                ] as $feature => $status) {
    ?>
                                    <div class="madeit-col-3">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo $feature; ?>
                                            </div>
                                            <div class="madeit-col">
                                                <?php if ($status == 'OK') {
        ?> <i class="fa fa-check madeit-text-success"></i> <?php
    } else {
        ?> <i class="fa fa-times madeit-text-danger"></i> <?php
    } ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
} ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <!-- REPO SCAN -->
                <div class="madeit-row">
                    <div class="madeit-col">
                        <div class="madeit-card">
                            <div class="madeit-card-body">
                                <h4 class="madeit-card-title">
                                    <?php echo esc_html(__('Scan summary', 'madeit_security')); ?>
                                </h4>
                                <h6 class="madeit-card-subtitle" id="repo-scan-time-ago">
                                    <?php if (isset($repoScanData['time'])) {
        printf(esc_html(__('Last scan %s ago.', 'madeit_security'), $this->timeAgo($repoScanData['time'])));
    } else {
        echo esc_html(__('No recent scan data found.', 'madeit_security'));
    } ?>
                                </h6>
                                <?php if ($this->defaultSettings['scan']['repo']['core'] || $this->defaultSettings['scan']['repo']['plugin'] || $this->defaultSettings['scan']['repo']['theme']) {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col madeit-text-center">
                                                <p class="madeit-card-title" id="repo-scan-core-status">
                                                    <?php if (isset($repoScanData['core']['success'])) {
            if ($repoScanData['core']['success'] == 1) {
                ?>
                                                            <i class="fa fa-check madeit-text-success"></i>
                                                            <?php
            } else {
                ?>
                                                            <i class="fa fa-times madeit-text-danger"></i>
                                                            <?php
            }
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('WordPress Core', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col  madeit-text-center">
                                                <p class="madeit-card-title" id="repo-scan-plugins-status">
                                                    <?php if (isset($repoScanData['plugin']['success'])) {
            if ($repoScanData['plugin']['success'] == 1) {
                ?>
                                                            <i class="fa fa-check madeit-text-success"></i>
                                                            <?php
            } else {
                ?>
                                                            <i class="fa fa-times madeit-text-danger"></i>
                                                            <?php
            }
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Plugins', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col madeit-text-center">
                                                <p class="madeit-card-title" id="repo-scan-themes-status">
                                                   <?php if (isset($repoScanData['theme']['success'])) {
            if ($repoScanData['theme']['success'] == 1) {
                ?>
                                                            <i class="fa fa-check madeit-text-success"></i>
                                                            <?php
            } else {
                ?>
                                                            <i class="fa fa-times madeit-text-danger"></i>
                                                            <?php
            }
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Themes', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" class="card-link do-repo-scan"><?php echo esc_html(__('Do scan now', 'madeit_security')); ?></a>
                                <?php
    } else {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Scanning changes against repo disabled.', 'madeit_security')); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
    } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END REPO SCAN -->
                <!-- BACKUP -->
                <div class="madeit-row" style="margin-top: 20px;">
                    <div class="madeit-col">
                        <div class="madeit-card">
                            <div class="madeit-card-body">
                                <h4 class="madeit-card-title">
                                    <?php echo esc_html(__('Backup summary', 'madeit_security')); ?>
                                </h4>
                                <h6 class="madeit-card-subtitle" id="backup-time-ago">
                                    <?php if (isset($backupExecutionData['time'])) {
        printf(__('Last backup %s ago.', 'madeit_security'), $this->timeAgo($backupExecutionData['time']));
    } else {
        echo esc_html(__('No recent backup completed.', 'madeit_security'));
    } ?>
                                </h6>
                                <?php if ($this->defaultSettings['maintenance']['backup']) {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col-3 madeit-text-center">
                                                <p class="madeit-card-title" id="backup-status">
                                                    <?php if (isset($backupExecutionData['time'])) {
            if ($backupExecutionData['status'] == true) {
                ?>
                                                            <i class="fa fa-check madeit-text-success"></i>
                                                            <?php
            } else {
                ?>
                                                            <i class="fa fa-times madeit-text-danger"></i>
                                                            <?php
            }
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Backup', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col madeit-text-center">
                                                <h3>
                                                    <?php echo esc_html(__('Backup info', 'madeit_security')); ?>
                                                </h3>
                                                <div id="backup-result">
                                                    <?php echo esc_html(__('Pre check:'), 'madeit_security'); ?>
                                                    <?php if ($backupExecutionData['preCheck']) {
            ?><i class="fa fa-check madeit-text-success"></i><?php
        } else {
            echo $backupExecutionData['check_error'];
        } ?>
                                                    <br>
                                                    
                                                    <?php echo esc_html(__('Download backup:'), 'madeit_security'); ?>
                                                    <?php if (!empty($backupExecutionData['url'])) {
            ?>
                                                        <a href="<?php echo esc_html($backupExecutionData['url']); ?>"><?php echo esc_html(__('Download', 'madeit_security')); ?></a>
                                                    <?php
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                    <br>
                                                    
                                                    <?php echo esc_html(__('Runtime:'), 'madeit_security'); ?>
                                                    <?php if (!empty($backupExecutionData['runtime'])) {
            ?>
                                                        <?php printf(__('%ss', 'madeit_security'), round($backupExecutionData['runtime'])); ?>
                                                    <?php
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                    <br>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" class="card-link do-backup"><?php echo esc_html(__('Do backup', 'madeit_security')); ?></a>
                                <?php
    } else {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Automatic backup disabled.', 'madeit_security')); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
    } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END REPO SCAN -->
            </div>
            <div class="madeit-col">
                
                <!-- UPDATE SCAN -->
                <div class="madeit-row">
                    <div class="madeit-col">
                        <div class="madeit-card">
                            <div class="madeit-card-body">
                                <h4 class="madeit-card-title">
                                    <?php echo esc_html(__('Update summary', 'madeit_security')); ?>
                                </h4>
                                <h6 class="madeit-card-subtitle" id="update-scan-time-ago">
                                    <?php if (isset($updateScanData['time'])) {
        printf(__('Last scan %s ago.', 'madeit_security'), $this->timeAgo($updateScanData['time']));
    } else {
        echo esc_html(__('No recent scan data found.', 'madeit_security'));
    } ?>
                                </h6>
                                <?php if ($this->defaultSettings['scan']['update'] || $this->defaultSettings['maintenance']['enable']) {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col  madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-core-status">
                                                    <?php if (isset($updateScanData['core'])) {
            echo esc_html($updateScanData['core']);
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('WordPress Core', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col  madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-plugins-status">
                                                    <?php if (isset($updateScanData['plugin'])) {
            echo esc_html($updateScanData['plugin']);
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Plugins', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-themes-status">
                                                   <?php if (isset($updateScanData['theme'])) {
            echo esc_html($updateScanData['theme']);
        } else {
            echo esc_html(__('N/A', 'madeit_security'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Themes', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" class="card-link do-update-scan"><?php echo esc_html(__('Do scan now', 'madeit_security')); ?></a>
                                <?php
    } else {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Scanning plugin updates disabled.', 'madeit_security')); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
    } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END UPDATE SCAN -->
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.do-repo-scan').click(function(e) {
            e.preventDefault();
            $(this).hide();
            $('#repo-scan-core-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#repo-scan-plugins-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#repo-scan-themes-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            var data = {
                'action': 'madeit_security_repo_scan',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                $('.do-repo-scan').show();
                $('#repo-scan-time-ago').html('<?php printf(__('Last scan %s ago.', 'madeit_security'), '1s'); ?>');
                $('#repo-scan-core-status').html(response.core.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                $('#repo-scan-plugins-status').html(response.plugin.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                $('#repo-scan-themes-status').html(response.theme.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
            }, 'json');
        });
        
        $('.do-update-scan').click(function(e) {
            e.preventDefault();
            $(this).hide();
            $('#update-scan-core-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#update-scan-plugins-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#update-scan-themes-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            var data = {
                'action': 'madeit_security_update_scan',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                $('.do-update-scan').show();
                $('#update-scan-time-ago').html('<?php printf(__('Last scan %s ago.', 'madeit_security'), '1s'); ?>');
                $('#update-scan-core-status').html(response.core);
                $('#update-scan-plugins-status').html(response.plugin);
                $('#update-scan-themes-status').html(response.theme);
            }, 'json');
        });
        
        $('.do-backup').click(function(e) {
            e.preventDefault();
            $(this).hide();
            $('#backup-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#backup-result').html('<i class="fa fa-spinner fa-pulse"></i>');
            var data = {
                'action': 'madeit_security_backup',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                $('.do-backup').show();
                $('#backup-time-ago').html('<?php printf(__('Last backup %s ago.', 'madeit_security'), '1s'); ?>');
                $('#backup-status').html(response.status ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                $('#backup-result').html(
                    '<?php echo esc_html(__('Pre check:'), 'madeit_security'); ?>' +
                    (response.preCheck ? '<i class="fa fa-check madeit-text-success"></i>' : response.check_error) + '<br>' +
                    
                    '<?php echo esc_html(__('Download backup:'), 'madeit_security'); ?>' +
                    (response.url.length > 0 ? 
                        '<a href="' + response.url + '"><?php echo esc_html(__('Download', 'madeit_security')); ?></a>' :
                        '<?php echo esc_html(__('N/A', 'madeit_security')); ?>') + '<br>' +
                    
                    '<?php echo esc_html(__('Runtime:'), 'madeit_security'); ?>' +
                    (response.runtime > 0 ? Math.round(response.runtime * 100) / 100 : '<?php echo esc_html(__('N/A', 'madeit_security')); ?>') + '<br>'
                );
            }, 'json');
        });
    });
</script>