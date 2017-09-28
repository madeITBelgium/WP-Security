<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
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
                <h1><?php echo esc_html(__('Security Scan results', 'madeit_security')); ?></h1>
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
                                    <?php if(isset($repoScanData['time'])) {
                                        printf(__('Last scan %s ago.', 'madeit_security'), $this->timeAgo($repoScanData['time']));
                                    } else {
                                        echo esc_html(__('No recent scan data found.', 'madeit_security'));
                                    } ?>
                                </h6>
                                <?php if($this->defaultSettings['scan']['repo']['core'] || $this->defaultSettings['scan']['repo']['plugin'] || $this->defaultSettings['scan']['repo']['theme']) { ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col madeit-text-center">
                                                <p class="madeit-card-title" id="repo-scan-core-status">
                                                    <?php if(isset($repoScanData['core']['success'])) {
                                                        if($repoScanData['core']['success'] == 1) {
                                                            ?>
                                                            <i class="fa fa-check madeit-text-success"></i>
                                                            <?php
                                                        }
                                                        else {
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
                                                    <?php if(isset($repoScanData['plugin']['success'])) {
                                                        if($repoScanData['plugin']['success'] == 1) {
                                                            ?>
                                                            <i class="fa fa-check madeit-text-success"></i>
                                                            <?php
                                                        }
                                                        else {
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
                                                   <?php if(isset($repoScanData['theme']['success'])) {
                                                        if($repoScanData['theme']['success'] == 1) {
                                                            ?>
                                                            <i class="fa fa-check madeit-text-success"></i>
                                                            <?php
                                                        }
                                                        else {
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
                                <?php } else { ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Scanning changes against repo disabled.', 'madeit_security')); ?>
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
                                    <?php echo esc_html(__('Update summary', 'madeit_security')); ?>
                                </h4>
                                <h6 class="madeit-card-subtitle" id="update-scan-time-ago">
                                    <?php if(isset($updateScanData['time'])) {
                                        printf(__('Last scan %s ago.', 'madeit_security'), $this->timeAgo($updateScanData['time']));
                                    } else {
                                        echo esc_html(__('No recent scan data found.', 'madeit_security'));
                                    } ?>
                                </h6>
                                <?php if($this->defaultSettings['scan']['update'] || $this->defaultSettings['maintenance']['enable']) { ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col  madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-core-status">
                                                    <?php if(isset($updateScanData['core'])) { echo esc_html($updateScanData['core']); } else { echo esc_html(__('N/A', 'madeit_security')); } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('WordPress Core', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col  madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-plugins-status">
                                                    <?php if(isset($updateScanData['plugin'])) { echo esc_html($updateScanData['plugin']); } else { echo esc_html(__('N/A', 'madeit_security')); } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Plugins', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                            <div class="madeit-col madeit-text-center">
                                                <p class="madeit-card-title" id="update-scan-themes-status">
                                                   <?php if(isset($updateScanData['theme'])) { echo esc_html($updateScanData['theme']); } else { echo esc_html(__('N/A', 'madeit_security')); } ?>
                                                </p>
                                                <p>
                                                    <?php echo esc_html(__('Themes', 'madeit_security')); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" class="card-link do-update-scan"><?php echo esc_html(__('Do scan now', 'madeit_security')); ?></a>
                                <?php } else { ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Scanning plugin updates disabled.', 'madeit_security')); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
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
                            <?php echo esc_html(__('Scan result', 'madeit_security')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <h6 class="madeit-card-subtitle" id="repo-scan-time-ago">
                                    <?php if(isset($repoScanData['time'])) {
                                        printf(__('Last scan %s ago.', 'madeit_security'), $this->timeAgo($repoScanData['time']));
                                    } else {
                                        echo esc_html(__('No recent scan data found.', 'madeit_security'));
                                    } ?>
                                </h6>
                                <?php if($this->defaultSettings['scan']['repo']['core'] || $this->defaultSettings['scan']['repo']['plugin'] || $this->defaultSettings['scan']['repo']['theme']) { ?>
                                    <div class="card-text" style="margin-top: 20px; margin-bottom: 20px; width: 100%">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php if(isset($repoScanData['time'])) { ?>
                                                    <div class="madeit-row" style="">
                                                        <div class="madeit-col-1 madeit-text-center">
                                                            <?php if(isset($repoScanData['core']['success'])) {
                                                                if($repoScanData['core']['success'] == 1) {
                                                                    ?>
                                                                    <i class="fa fa-2x fa-check madeit-text-success"></i>
                                                                    <?php
                                                                }
                                                                else {
                                                                    ?>
                                                                    <i class="fa fa-2x fa-times madeit-text-danger"></i>
                                                                    <?php
                                                                }
                                                            } else {
                                                                echo esc_html(__('N/A', 'madeit_security'));
                                                            } ?>
                                                        </div>
                                                        <div class="madeit-col">
                                                            <h3 style="margin: 0">
                                                                <?php echo esc_html(__('WordPress Core', 'madeit_security')); ?>
                                                            </h3>
                                                        </div>
                                                    </div>
                                                    <?php foreach($repoScanData['core']['plugins'] as $result) { ?>
                                                        <div class="madeit-row">
                                                            <div class="madeit-col">
                                                                <?php echo esc_html(__($result, 'madeit_security')); ?>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                
                                                    <div class="madeit-row" style="margin-top: 20px;">
                                                        <div class="madeit-col-1 madeit-text-center">
                                                            <?php if(isset($repoScanData['plugin']['success'])) {
                                                                if($repoScanData['plugin']['success'] == 1) {
                                                                    ?>
                                                                    <i class="fa fa-2x fa-check madeit-text-success"></i>
                                                                    <?php
                                                                }
                                                                else {
                                                                    ?>
                                                                    <i class="fa fa-2x fa-times madeit-text-danger"></i>
                                                                    <?php
                                                                }
                                                            } else {
                                                                echo esc_html(__('N/A', 'madeit_security'));
                                                            } ?>
                                                        </div>
                                                        <div class="madeit-col">
                                                            <h3 style="margin: 0">
                                                                <?php echo esc_html(__('Plugins', 'madeit_security')); ?>
                                                            </h3>
                                                        </div>
                                                    </div>
                                                    <?php foreach($repoScanData['plugin']['plugins'] as $plugin => $result) { ?>
                                                        <div class="madeit-row">
                                                            <div class="madeit-col-2">
                                                                <?php echo esc_html($plugin); ?>
                                                            </div>
                                                            <div class="madeit-col">
                                                                <?php
                                                                if(is_array($result)) {
                                                                    $notFound = 0;
                                                                    $notFoundFiles = [];
                                                                    $changed = 0;
                                                                    $changedFiles = [];
                                                                    foreach($result as $file => $error) {
                                                                        if($error == "File changed") {
                                                                            $changedFiles[] = $file;
                                                                            $changed++;
                                                                        }
                                                                        elseif($error == "File not exist") {
                                                                            $notFoundFiles[] = $file;
                                                                            $notFound++;
                                                                        }
                                                                    } ?>
                                                                    <?php if($notFound > 0) { ?>
                                                                        <?php printf(__('<a href="%s">%s files</a> on your system that not exist in the original version.', 'madeit_security'), "admin.php?page=madeit_security_scan&notexist=" . $plugin, $notFound); ?>
                                                                    <?php } ?>
                                                                    <?php if($changed > 0) { ?>
                                                                        <?php printf(__('<a href="%s">%s files</a> on your system are different then the original version.', 'madeit_security'), "admin.php?page=madeit_security_scan&changes=" . $plugin, $changed); ?>
                                                                    <?php }
                                                                }
                                                                else {
                                                                    echo esc_html(__($result, 'madeit_security'));
                                                                } ?>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                
                                                    <div class="madeit-row" style="margin-top: 20px;">
                                                        <div class="madeit-col-1 madeit-text-center">
                                                            <?php if(isset($repoScanData['theme']['success'])) {
                                                                if($repoScanData['theme']['success'] == 1) {
                                                                    ?>
                                                                    <i class="fa fa-2x fa-check madeit-text-success"></i>
                                                                    <?php
                                                                }
                                                                else {
                                                                    ?>
                                                                    <i class="fa fa-2x fa-times madeit-text-danger"></i>
                                                                    <?php
                                                                }
                                                            } else {
                                                                echo esc_html(__('N/A', 'madeit_security'));
                                                            } ?>
                                                        </div>
                                                        <div class="madeit-col">
                                                            <h3 style="margin: 0">
                                                                <?php echo esc_html(__('Themes', 'madeit_security')); ?>
                                                            </h3>
                                                        </div>
                                                    </div>
                                                    <?php foreach($repoScanData['theme']['themes'] as $theme => $result) { ?>
                                                        <div class="madeit-row">
                                                            <div class="madeit-col-2">
                                                                <?php echo esc_html($theme); ?>
                                                            </div>
                                                            <div class="madeit-col">
                                                                <?php
                                                                if(is_array($result)) {
                                                                    $notFound = 0;
                                                                    $notFoundFiles = [];
                                                                    $changed = 0;
                                                                    $changedFiles = [];
                                                                    foreach($result as $file => $error) {
                                                                        if($error == "File changed") {
                                                                            $changedFiles[] = $file;
                                                                            $changed++;
                                                                        }
                                                                        elseif($error == "File not exist") {
                                                                            $notFoundFiles[] = $file;
                                                                            $notFound++;
                                                                        }
                                                                    } ?>
                                                                    <?php if($notFound > 0) { ?>
                                                                        <?php printf(__('%s files on your system that not exist in the original version.', 'madeit_security'), $notFound); ?>
                                                                    <?php } ?>
                                                                    <?php if($notFound > 0 && $changed > 0) { echo "<br>"; } ?>
                                                                    <?php if($changed > 0) { ?>
                                                                        <?php printf(__('%s files on your system are different then the original version.', 'madeit_security'), $changed); ?>
                                                                    <?php }
                                                                }
                                                                else {
                                                                    echo esc_html(__($result, 'madeit_security'));
                                                                } ?>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <?php echo esc_html(__('No recent scan data found.', 'madeit_security')); ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('Scanning changes against repo disabled.', 'madeit_security')); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
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
                $('#repo-scan-time-ago').html('<?php printf(__("Last scan %s ago.", "madeit_security"), '1s'); ?>');
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
                $('#update-scan-time-ago').html('<?php printf(__("Last scan %s ago.", "madeit_security"), '1s'); ?>');
                $('#update-scan-core-status').html(response.core);
                $('#update-scan-plugins-status').html(response.plugin);
                $('#update-scan-themes-status').html(response.theme);
            }, 'json');
        });
    });
</script>