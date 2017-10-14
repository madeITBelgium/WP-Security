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
                <h1><?php echo esc_html(__('Security Scan results', 'wp-security-by-made-it')); ?></h1>
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
                                <?php if ($this->defaultSettings['scan']['repo']['core'] || $this->defaultSettings['scan']['repo']['plugin'] || $this->defaultSettings['scan']['repo']['theme']) { ?>
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
                                <?php } else { ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Scanning changes against repo disabled.', 'wp-security-by-made-it')); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
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
        
        
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('Scan result', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <h6 class="madeit-card-subtitle" id="repo-scan-time-ago">
                                    <?php if (isset($lastScan['start_time'])) {
        printf(__('Last scan %s ago.', 'wp-security-by-made-it'), $this->timeAgo($lastScan['start_time']));
    } else {
        echo esc_html(__('No recent scan data found.', 'wp-security-by-made-it'));
    } ?>
                                </h6>
                                <?php if ($this->defaultSettings['scan']['repo']['core'] || $this->defaultSettings['scan']['repo']['plugin'] || $this->defaultSettings['scan']['repo']['theme']) {
        ?>
                                    <div class="card-text" style="margin-top: 20px; margin-bottom: 20px; width: 100%">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php if (isset($lastScan['start_time'])) {
            ?>
                                                    <div class="madeit-row" style="">
                                                        <div class="madeit-col-1 madeit-text-center">
                                                            <?php if (isset($lastScan['result']['core']['success'])) {
                if ($lastScan['result']['core']['success'] == 1) {
                    ?>
                                                                    <i class="fa fa-2x fa-check madeit-text-success"></i>
                                                                    <?php
                } else {
                    ?>
                                                                    <i class="fa fa-2x fa-times madeit-text-danger"></i>
                                                                    <?php
                }
            } else {
                echo esc_html(__('N/A', 'wp-security-by-made-it'));
            } ?>
                                                        </div>
                                                        <div class="madeit-col">
                                                            <h3 style="margin: 0">
                                                                <?php echo esc_html(__('WordPress Core', 'wp-security-by-made-it')); ?>
                                                            </h3>
                                                        </div>
                                                    </div>
                                                    <?php /* foreach ($repoScanData['core']['plugins'] as $result) { ?>
                                                        <div class="madeit-row">
                                                            <div class="madeit-col">
                                                                <?php echo esc_html($result); ?>
                                                            </div>
                                                        </div>
                                                    <?php  } */ ?>
                                                    <div class="madeit-row" style="margin-top: 20px;">
                                                        <div class="madeit-col-1 madeit-text-center">
                                                            <?php if (isset($lastScan['result']['plugin']['success'])) {
                if ($lastScan['result']['plugin']['success'] == 1) {
                    ?>
                                                                    <i class="fa fa-2x fa-check madeit-text-success"></i>
                                                                    <?php
                } else {
                    ?>
                                                                    <i class="fa fa-2x fa-times madeit-text-danger"></i>
                                                                    <?php
                }
            } else {
                echo esc_html(__('N/A', 'wp-security-by-made-it'));
            } ?>
                                                        </div>
                                                        <div class="madeit-col">
                                                            <h3 style="margin: 0">
                                                                <?php echo esc_html(__('Plugins', 'wp-security-by-made-it')); ?>
                                                            </h3>
                                                        </div>
                                                    </div>
                                                    <?php
                                                        foreach ($pluginScanData as $plugin => $files) { ?>
                                                            <div class="madeit-row">
                                                                <div class="madeit-col-3">
                                                                    <?php echo esc_html($plugin); ?>
                                                                </div>
                                                                <div class="madeit-col">
                                                                    <?php 
                                                                    if (is_array($files)) {
                                                                        if (isset($files['File not exist in repo']) && count($files['File not exist in repo']) > 0) {
                                                                            printf(__('<a href="%s">%s files</a> on your system that not exist in the original version.', 'wp-security-by-made-it'), 'admin.php?page=madeit_security_scan&notexist='.$plugin, count($files['File not exist in repo']));
                                                                        }
                                                                        if (isset($files['File not equal to repo']) && count($files['File not equal to repo']) > 0) {
                                                                            printf(__('<a href="%s">%s files</a> on your system are different then the original version.', 'wp-security-by-made-it'), 'admin.php?page=madeit_security_scan&changes='.$plugin, count($files['File not equal to repo']));
                                                                        }
                                                                        if (isset($files['File required']) && count($files['File required']) > 0) {
                                                                            printf(__('<a href="%s">%s files</a> on your system are deleted but are required in the plugin.', 'wp-security-by-made-it'), 'admin.php?page=madeit_security_scan&required='.$plugin, count($files['File required']));
                                                                        }
                                                                        if (isset($files['File required and changed']) && count($files['File required and changed']) > 0) {
                                                                            printf(__('<a href="%s">%s files</a> on your system are deleted but are required in the plugin.', 'wp-security-by-made-it'), 'admin.php?page=madeit_security_scan&required='.$plugin, count($files['File required and changed']));
                                                                        }
                                                                    } else {
                                                                        echo esc_html(__('Some files of the plugin are not equal to the orignal file. Disable fast scanning to check which file.', 'wp-security-by-made-it'));
                                                                    } ?>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                
                                                    <div class="madeit-row" style="margin-top: 20px;">
                                                        <div class="madeit-col-1 madeit-text-center">
                                                            <?php if (isset($lastScan['result']['theme']['success'])) {
                if ($lastScan['result']['theme']['success'] == 1) {
                    ?>
                                                                    <i class="fa fa-2x fa-check madeit-text-success"></i>
                                                                    <?php
                } else {
                    ?>
                                                                    <i class="fa fa-2x fa-times madeit-text-danger"></i>
                                                                    <?php
                }
            } else {
                echo esc_html(__('N/A', 'wp-security-by-made-it'));
            } ?>
                                                        </div>
                                                        <div class="madeit-col">
                                                            <h3 style="margin: 0">
                                                                <?php echo esc_html(__('Themes', 'wp-security-by-made-it')); ?>
                                                            </h3>
                                                        </div>
                                                    </div>
                                                    <?php foreach ($themeScanData as $theme => $result) {
                ?>
                                                        <div class="madeit-row">
                                                            <div class="madeit-col-2">
                                                                <?php echo esc_html($theme); ?>
                                                            </div>
                                                            <div class="madeit-col">
                                                                <?php
                                                                if (is_array($result)) {
                                                                    if (isset($files['File not exist in repo']) && count($files['File not exist in repo']) > 0) {
                                                                        printf(__('%s files on your system that not exist in the original version.', 'wp-security-by-made-it'), count($files['File not exist in repo']));
                                                                    }
                                                                } ?>
                                                            </div>
                                                        </div>
                                                    <?php
            } ?>
                                                <?php
        } else {
            ?>
                                                    <?php echo esc_html(__('No recent scan data found.', 'wp-security-by-made-it')); ?>
                                                <?php
        } ?>
                                            </div>
                                        </div>
                                    </div>
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
    });
</script>