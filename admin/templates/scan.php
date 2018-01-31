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
                                    $updates = 0;
        $updates += isset($updateScanData['core']) && is_numeric($updateScanData['core']) ? $updateScanData['core'] : 0;
        $updates += isset($updateScanData['plugin']) && is_numeric($updateScanData['plugin']) ? $updateScanData['plugin'] : 0;
        $updates += isset($updateScanData['theme']) && is_numeric($updateScanData['theme']) ? $updateScanData['theme'] : 0;
        if ($updates > 0) {
            ?>
                                         / <a href="#" class="card-link do-update-all"><?php echo esc_html(__('Update all', 'wp-security-by-made-it')); ?></a>
                                        <?php
        }
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
                            <small>
                                <h6 style="display:inline;">
                                    <?php echo sprintf(_n('%s issue found', '%s issues found', count($issues), 'wp-security-by-made-it'), count($issues)); ?>
                                </h6>
                            </small>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <div class="card-text" style="margin-top: 20px; margin-bottom: 20px; width: 100%">
                                    <div class="madeit-row">
                                        <div class="madeit-col">
                                            <?php foreach ($issues as $issue) {
        ?>
                                                <?php $pluginData = $this->getPluginInfoByFile($issue['filename_md5']); ?>
                                                <div class="madeit-row" id="issue-<?php echo $issue['id']; ?>" style="border-bottom: 1px solid #DDD; margin-left: 15px; margin-right: 15px; padding-bottom: 10px; <?php if($issue['issue_readed'] == null) { ?> background-color: #fdf6c8;<?php } ?>">
                                                    <h3 style="margin-bottom: 0; width: 100%; padding-left: 10px"><?php echo esc_html($issue['shortMsg']); ?> <small><?php echo sprintf(__('Issue created at %s', 'wp-security-by-made-it'), date('Y-m-d H:i:s', $issue['issue_created'])); ?></small></h3>
                                                    <div class="madeit-col">
                                                        <?php echo esc_html(__('Severity:', 'wp-security-by-made-it')); ?> <?php echo esc_html($this->getSeverityTxt($issue['severity'])); ?><br>
                                                        <?php echo esc_html(__('Plugin:', 'wp-security-by-made-it')); ?> <?php echo esc_html($pluginData['plugin_data']['name']); ?><br>
                                                        <?php echo esc_html($issue['longMsg']); ?><br>
                                                        <?php echo esc_html($issue['type']); ?><br>
                                                        <input type="checkbox" name="issue_id[]" class="issues" value="<?php echo $issue['id']; ?>" style="margin-right: 20px;" data-issue-type="<?php echo esc_html($issue['type']); ?>">
                                                        <?php if (in_array($issue['type'], [2, 3])) {
            ?>
                                                            <a href="admin.php?page=madeit_security_scan&changes=<?php echo $pluginData['plugin']; ?>&version=<?php echo $pluginData['version']; ?>&file=<?php echo $issue['filename_md5']; ?>"><?php echo esc_html(__('Compare file', 'wp-security-by-made-it')); ?></a>
                                                        <?php
        } ?>
                                                        <?php if (in_array($issue['type'], [5, 2, 3])) {
            ?>
                                                            <a href="admin.php?page=madeit_security_scan&changes=<?php echo $pluginData['plugin']; ?>&version=<?php echo $pluginData['version']; ?>&replace=<?php echo $nonceReplace; ?>&file=<?php echo $issue['filename_md5']; ?>"><?php echo esc_html(__('Restore file', 'wp-security-by-made-it')); ?></a>
                                                        <?php
        } ?>
                                                        <?php if (in_array($issue['type'], [6])) {
            ?>
                                                            <a href="admin.php?page=madeit_security_scan&changes=<?php echo $pluginData['plugin']; ?>&version=<?php echo $pluginData['version']; ?>&delete=<?php echo $nonceDelete; ?>&file=<?php echo $issue['filename_md5']; ?>"><?php echo esc_html(__('Delete file', 'wp-security-by-made-it')); ?></a>
                                                        <?php
        } ?>
                                                        <?php /*<a href="admin.php?page=madeit_security_scan&fix-issue=<?php echo $issue['id']; ?>"><?php echo esc_html(__('Fix issue', 'wp-security-by-made-it')); ?></a> */ ?>
                                                        <a href="admin.php?page=madeit_security_scan&ignore-issue=<?php echo $issue['id']; ?>" class="ignore-issue" data-id="<?php echo $issue['id']; ?>"><?php echo esc_html(__('Ignore issue', 'wp-security-by-made-it')); ?></a>
                                                        <?php if($issue['issue_readed'] == null) { ?>
                                                            <a href="admin.php?page=madeit_security_scan&read-issue=<?php echo $issue['id']; ?>" class="read-issue" data-id="<?php echo $issue['id']; ?>"><?php echo esc_html(__('Read issue', 'wp-security-by-made-it')); ?></a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php
    } ?>
                                        </div>
                                    </div>
                                    <div class="madeit-row" style="margin-top: 50px; margin-left: 15px; margin-right: 15px;">
                                        <div class="madeit-col">
                                            <input type="checkbox" value="" class="check_all"><?php _e('Select all', 'wp-security-by-made-it'); ?> 
                                            <a href="" class="issues-ignore-selected"><?php _e('Ignore issues', 'wp-security-by-made-it'); ?></a>
                                            <a href="" class="issues-read-selected"><?php _e('Read issues', 'wp-security-by-made-it'); ?></a>
                                        </div>
                                    </div>
                                </div>
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
        
        $('.issues-ignore-selected').click(function(e) {
            e.preventDefault();
            $('.issues').each(function() {
                if($(this).is(':checked')) {
                    var id = $(this).val();
                    $.get('admin.php?page=madeit_security_scan&ignore-issue=' + id, function(data) {
                        $('#issue-' + id).remove();
                    });
                }
            });
        });
        
        $('.ignore-issue').click(function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            $.get('admin.php?page=madeit_security_scan&ignore-issue=' + id, function(data) {
                $('#issue-' + id).remove();
            });
        });
        
        $('.issues-read-selected').click(function(e) {
            e.preventDefault();
            $('.issues').each(function() {
                if($(this).is(':checked')) {
                    var id = $(this).val();
                    $.get('admin.php?page=madeit_security_scan&read-issue=' + id, function(data) {
                        $('#issue-' + id).css('background', 'none');
                    });
                }
            });
        });
        
        $('.read-issue').click(function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            $.get('admin.php?page=madeit_security_scan&read-issue=' + id, function(data) {
                $('#issue-' + id).css('background', 'none');
            });
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
                    $('.scan-step').html('<?php echo __('Complete scan', 'wp-security-by-made-it'); ?>');
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
                    }
                    if(response.result.step >= 7) {
                        $('.scan-step').html('<?php echo __('Scan theme files', 'wp-security-by-made-it'); ?>');
                    }
                    if(response.result.step >= 8) {
                        $('.scan-step').html('<?php echo __('Scan core Vulnerabilities', 'wp-security-by-made-it'); ?>');
                        $('#repo-scan-core-status').html(response.result.result.core.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    }
                    if(response.result.step >= 9) {
                        $('.scan-step').html('<?php echo __('Scan plugin Vulnerabilities', 'wp-security-by-made-it'); ?>');
                        $('#repo-scan-plugins-status').html(response.result.result.plugin.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    }
                    if(response.result.step >= 10) {
                        $('.scan-step').html("<?php echo __('Scan theme Vulnerabilities', 'wp-security-by-made-it'); ?>");
                        $('#repo-scan-themes-status').html(response.result.result.theme.success ? '<i class="fa fa-check madeit-text-success"></i>' : '<i class="fa fa-times madeit-text-danger"></i>');
                    }
                    if(response.result.step >= 11) {
                        $('.scan-step').html('<?php echo __('Complete scan', 'wp-security-by-made-it'); ?>');
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
        
        $('.check_all').change(function(e) {
            if($(this).is(':checked')) {
                $('.issues').prop('checked', true);
            }
            else {
                $('.issues').prop('checked', false);
            }
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
                $('#update-scan-time-ago').html('<?php printf(__('Last scan %s ago.', 'wp-security-by-made-it'), '1s'); ?>');
                $('#update-scan-core-status').html(response.core);
                $('#update-scan-plugins-status').html(response.plugin);
                $('#update-scan-themes-status').html(response.theme);
            }, 'json');
        });
        
        $('.do-update-all').click(function(e) {
            e.preventDefault();
            $(this).attr('disabled', 'disabled');
            $('.do-update-scan').attr('disabled', 'disabled');
            $('#update-scan-error').hide();
            $('#update-scan-core-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#update-scan-plugins-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            $('#update-scan-themes-status').html('<i class="fa fa-spinner fa-pulse"></i>');
            var data = {
                'action': 'madeit_security_do_update',
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                //$('.do-update-all').removeAttr('disabled');
                $('.do-update-scan').removeAttr('disabled');
                if(response.success) {
                    $('#update-scan-time-ago').html('<?php printf(__('Last scan %s ago.', 'wp-security-by-made-it'), '1s'); ?>');
                    $('#update-scan-core-status').html(response.scan.core);
                    $('#update-scan-plugins-status').html(response.scan.plugin);
                    $('#update-scan-themes-status').html(response.scan.theme);
                }
                else {
                    //error loggin
                    $('#update-scan-error').show();
                    $('#update-scan-error').html('');
                    $.each(response.errored_plugins, function(plugin, error) {
                        $('#update-scan-error').append(plugin + ': ' + error + '<br>');
                        $('#update-scan-core-status').html(response.scan.core);
                        $('#update-scan-plugins-status').html(response.scan.plugin);
                        $('#update-scan-themes-status').html(response.scan.theme);
                    });
                }
            }, 'json');
        });
    });
</script>