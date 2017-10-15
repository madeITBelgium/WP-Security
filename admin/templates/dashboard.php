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
                <h1><?php echo esc_html(__('Security Dashboard', 'wp-security-by-made-it')); ?></h1>
            </div>
        </div>
        
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('Feature status', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <?php foreach ([
                                     __('Scan repo changes', 'wp-security-by-made-it') => wp_next_scheduled('madeit_security_loadfiles') ? 'OK' : 'NOK',
                                     __('Fast scan', 'wp-security-by-made-it') => $this->defaultSettings['scan']['fast'] ? 'OK' : 'NOK',
                                     __('Scan core changes', 'wp-security-by-made-it') => $this->defaultSettings['scan']['repo']['core'] ? 'OK' : 'NOK',
                                     __('Scan plugin changes', 'wp-security-by-made-it') => $this->defaultSettings['scan']['repo']['plugin'] ? 'OK' : 'NOK',
                                     __('Scan theme changes', 'wp-security-by-made-it') => $this->defaultSettings['scan']['repo']['theme'] ? 'OK' : 'NOK',
                                     __('Scan updates', 'wp-security-by-made-it') => $this->defaultSettings['scan']['update'] ? 'OK' : 'NOK',
                                     __('Maintenance by Made I.T.', 'wp-security-by-made-it') => $this->defaultSettings['maintenance']['enable'] ? 'OK' : 'NOK',
                                     __('Scan plugin updates', 'wp-security-by-made-it') => wp_next_scheduled('madeit_security_check_plugin_updates') ? 'OK' : 'NOK',
                                     __('Backup', 'wp-security-by-made-it') => wp_next_scheduled('madeit_security_backup') ? 'OK' : 'NOK',
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
                                    <?php echo esc_html(__('Scan summary', 'wp-security-by-made-it')); ?>
                                </h4>
                                <h6 class="madeit-card-subtitle" id="repo-scan-time-ago">
                                    <?php echo esc_html(__('No recent scan data found.', 'wp-security-by-made-it')); ?>
                                </h6>
                                <?php if ($this->defaultSettings['scan']['repo']['core'] || $this->defaultSettings['scan']['repo']['plugin'] || $this->defaultSettings['scan']['repo']['theme']) {
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
                                    <span class="scan-step pull-right"></span>
                                    <a href="#" class="card-link do-repo-scan"><?php echo esc_html(__('Do scan now', 'wp-security-by-made-it')); ?></a>  <a href="#" class="card-link stop-repo-scan"><?php echo esc_html(__('Stop scan now', 'wp-security-by-made-it')); ?></a>
                                <?php
    } else {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Scanning changes against repo disabled.', 'wp-security-by-made-it')); ?>
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
                                    <?php echo esc_html(__('Backup summary', 'wp-security-by-made-it')); ?>
                                </h4>
                                <h6 class="madeit-card-subtitle" id="backup-time-ago">
                                    <?php echo esc_html(__('No recent backup completed.', 'wp-security-by-made-it')); ?>
                                </h6>
                                <?php if ($this->defaultSettings['maintenance']['backup']) {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col-3 madeit-text-center">
                                                <p class="madeit-card-title" id="backup-status">
                                                    <?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Backup', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col madeit-text-center">
                                                <h3>
                                                    <?php echo esc_html(__('Backup info', 'wp-security-by-made-it')); ?>
                                                </h3>
                                                <div id="backup-result">
                                                    <?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>
                                                    <br>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" class="card-link do-backup"><?php echo esc_html(__('Do backup', 'wp-security-by-made-it')); ?></a> <a href="#" class="card-link stop-backup"><?php echo esc_html(__('Stop backup', 'wp-security-by-made-it')); ?></a>
                                <?php
    } else {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Automatic backup disabled.', 'wp-security-by-made-it')); ?>
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
                                    <?php echo esc_html(__('Update summary', 'wp-security-by-made-it')); ?>
                                </h4>
                                <h6 class="madeit-card-subtitle" id="update-scan-time-ago">
                                    <?php if (isset($updateScanData['time'])) {
        printf(__('Last scan %s ago.', 'wp-security-by-made-it'), $this->timeAgo($updateScanData['time']));
    } else {
        echo esc_html(__('No recent scan data found.', 'wp-security-by-made-it'));
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
            echo esc_html(__('N/A', 'wp-security-by-made-it'));
        } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('WordPress Core', 'wp-security-by-made-it')); ?>
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
                                                    <?php echo esc_html(__('Plugins', 'wp-security-by-made-it')); ?>
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
                                                    <?php echo esc_html(__('Themes', 'wp-security-by-made-it')); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" class="card-link do-update-scan"><?php echo esc_html(__('Do scan now', 'wp-security-by-made-it')); ?></a>
                                <?php
    } else {
        ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Scanning plugin updates disabled.', 'wp-security-by-made-it')); ?>
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
        var interval = null;
        $('.do-repo-scan').click(function(e) {
            e.preventDefault();
            $(this).hide();
            $('#repo-scan-core-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#repo-scan-plugins-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#repo-scan-themes-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            var data = {
                'action': 'madeit_security_start_scan',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                $('.stop-repo-scan').show();
                interval = setInterval(function(){ checkScanResult(); }, 5000);
            }, 'json');
        });
        
        $('.stop-repo-scan').click(function(e) {
            e.preventDefault();
            $(this).hide();
            var data = {
                'action': 'madeit_security_stop_scan',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                clearInterval(interval);
                $('.stop-repo-scan').hide();
                $('.do-repo-scan').show();
                $('#repo-scan-core-status').html('<?php  echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>');
                $('#repo-scan-plugins-status').html('<?php  echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>');
                $('#repo-scan-themes-status').html('<?php  echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>');
            }, 'json');
        });
        
        function checkScanResult() {
            var data = {
                'action': 'madeit_security_check_scan',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                if(response.completed == true) {
                    clearInterval(interval);
                    $('.stop-repo-scan').hide();
                    $('.do-repo-scan').show();
                    $('#repo-scan-time-ago').html(response.time_ago);
                    $('#repo-scan-core-status').html(response.result.result.core.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    $('#repo-scan-plugins-status').html(response.result.result.plugin.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    $('#repo-scan-themes-status').html(response.result.result.theme.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    interval = null;
                }
                else if(response.running == true) {
                    if(response.result.step >= 0) {
                        $('.scan-step').html('<?php echo __('Starting scan', 'wp-security-by-made-it'); ?>');
                    }
                    if(response.result.step >= 1) {
                        $('.scan-step').html('<?php echo __('Loading core files', 'wp-security-by-made-it'); ?>');
                    }
                    if(response.result.step >= 2) {
                        $('.scan-step').html('<?php echo __('Loading plugin files', 'wp-security-by-made-it'); ?>');
                    }
                    if(response.result.step >= 3) {
                        $('.scan-step').html('<?php echo __('Loading theme files', 'wp-security-by-made-it'); ?>');
                    }
                    if(response.result.step >= 4) {
                        $('.scan-step').html('<?php echo __('Prepare to start scan', 'wp-security-by-made-it'); ?>');
                    }
                    if(response.result.step >= 5) {
                        $('.scan-step').html('<?php echo __('Scan core files', 'wp-security-by-made-it'); ?>');
                    }
                    if(response.result.step >= 6) {
                        $('.scan-step').html('<?php echo __('Scan plugin files', 'wp-security-by-made-it'); ?>');
                        $('#repo-scan-core-status').html(response.result.result.core.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    }
                    if(response.result.step >= 7) {
                        $('.scan-step').html('<?php echo __('Scan theme files', 'wp-security-by-made-it'); ?>');
                        $('#repo-scan-plugins-status').html(response.result.result.plugin.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    }
                    if(response.result.step >= 8) {
                        $('.scan-step').html('<?php echo __('Complete scan', 'wp-security-by-made-it'); ?>');
                        $('#repo-scan-themes-status').html(response.result.result.theme.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    }
                    if(interval == null) {
                        $('.stop-repo-scan').show();
                        $('.do-repo-scan').hide();
                        $('#repo-scan-core-status').html('<i class="fa fa-spinner fa-pulse"></i>');
                        $('#repo-scan-plugins-status').html('<i class="fa fa-spinner fa-pulse"></i>');
                        $('#repo-scan-themes-status').html('<i class="fa fa-spinner fa-pulse"></i>');
                        $('.do-repo-scan').hide();
                        interval = setInterval(function(){ checkScanResult(); }, 5000);
                    }
                    $('#repo-scan-time-ago').html(response.time_ago);
                    
                }
                else if(response.running == false && response.completed == false) {
                    $('.stop-repo-scan').hide();
                    $('#repo-scan-core-status').html('<?php  echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>');
                    $('#repo-scan-plugins-status').html('<?php  echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>');
                    $('#repo-scan-themes-status').html('<?php  echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>');
                }
            }, 'json');
        }
        checkScanResult();
        
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
                $('#update-scan-time-ago').html('<?php printf(__('Last scan %s ago.', 'wp-security-by-made-it'), '1s'); ?>');
                $('#update-scan-core-status').html(response.core);
                $('#update-scan-plugins-status').html(response.plugin);
                $('#update-scan-themes-status').html(response.theme);
            }, 'json');
        });
        
        
        var backupInterval = null;
        $('.do-backup').click(function(e) {
            e.preventDefault();
            $('.do-backup').hide();
            $('.stop-backup').show();
            $('#backup-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#backup-result').html('<i class="fa fa-spinner fa-pulse"></i>');
            var data = {
                'action': 'madeit_security_backup',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                backupInterval = setInterval(function(){ checkBackup(); }, 15000);
            }, 'json');
        });
        
        $('.stop-backup').click(function(e) {
            e.preventDefault();
            $(this).hide();
            var data = {
                'action': 'madeit_security_backup_stop',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                clearInterval(interval);
                $('.stop-backup').hide();
                $('.do-backup').show();
                $('#backup-status').html('<?php  echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>');
                $('#backup-result').html('<?php  echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>');
            }, 'json');
        });
        
        function checkBackup() {
            var data = {
                'action': 'madeit_security_backup_check',
            };
            
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                if(response.completed == true) {
                    clearInterval(backupInterval);
                    $('.do-backup').show();
                    $('.stop-backup').hide();
                    $('#backup-time-ago').html(response.time_ago);
                    $('#backup-status').html(response.result.done && response.result.check_error == null ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    $('#backup-result').html(
                        '<?php echo esc_html(__('Pre check:'), 'wp-security-by-made-it'); ?>' +
                        (response.result.preCheck ? '<i class="fa fa-check madeit-text-success"></i>' : response.result.check_error) + '<br>' +

                        '<?php echo esc_html(__('Download backup:'), 'wp-security-by-made-it'); ?>' +
                        (response.result.url.length > 0 ? 
                            '<a href="' + response.result.url + '"><?php echo esc_html(__('Download', 'wp-security-by-made-it')); ?></a>' :
                            '<?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>') + '<br>' +

                        '<?php echo esc_html(__('Runtime:'), 'wp-security-by-made-it'); ?>' +
                        (response.result.runtime > 0 ? Math.round(response.result.runtime * 100) / 100 : '<?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>') + '<br>'
                    );
                }
                else if(response.running) {
                    if(backupInterval == null) {
                        backupInterval = setInterval(function(){ checkBackup(); }, 15000);
                    }
                    $('.do-backup').hide();
                    $('.stop-backup').show();
                    $('#backup-status').html('<i class="fa fa-spinner fa-pulse"></i>');
                    $('#backup-result').html('<i class="fa fa-spinner fa-pulse"></i>');
                }
                else {
                    $('.do-backup').show();
                    $('.stop-backup').hide();
                    $('#backup-time-ago').html(response.time_ago);
                    $('#backup-status').html(response.result.status ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    $('#backup-result').html(
                        '<?php echo esc_html(__('Pre check:'), 'wp-security-by-made-it'); ?>' +
                        (response.result.preCheck ? '<i class="fa fa-check madeit-text-success"></i>' : response.result.check_error) + '<br>' +

                        '<?php echo esc_html(__('Download backup:'), 'wp-security-by-made-it'); ?>' +
                        (response.result.url.length > 0 ? 
                            '<a href="' + response.result.url + '"><?php echo esc_html(__('Download', 'wp-security-by-made-it')); ?></a>' :
                            '<?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>') + '<br>' +

                        '<?php echo esc_html(__('Runtime:'), 'wp-security-by-made-it'); ?>' +
                        (response.result.runtime > 0 ? Math.round(response.result.runtime * 100) / 100 : '<?php echo esc_html(__('N/A', 'wp-security-by-made-it')); ?>') + '<br>'
                    );
                }
            }, 'json');
        }
        checkBackup();
    });
</script>