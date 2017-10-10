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
                <h1><?php echo esc_html(__('Changed files', 'wp-security-by-made-it')); ?></h1>
            </div>
        </div>
        
        <?php
        if (isset($fileReplacedSuccesfull)) {
            ?>
            <div class="updated">
                <p>
                    <strong>
                        <?php
                        printf(__('The file %s is replaced with the original version.', 'wp-security-by-made-it'), $file); ?>
                    </strong>
                </p>
            </div>
            <?php
        } ?>
        
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php printf(esc_html(__('Changed files of %s', 'wp-security-by-made-it')), $plugin); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <?php if (count($files) > 0) {
            ?>
                                    <div class="card-text" style="margin-top: 20px; margin-bottom: 20px; width: 100%">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php
                                                foreach ($files as $file) {
                                                    printf(__('Compare the file <a href="%s">%s</a> with the original version.', 'wp-security-by-made-it'), 'admin.php?page=madeit_security_scan&changes='.$plugin.'&version='.$version.'&file='.$file, $file);
                                                    if (!$this->isFileIgnored($plugin, $file)) {
                                                        echo ' <a href="admin.php?page=madeit_security_scan&changes='.$plugin.'&version='.$version.'&ignore='.$nonce.'&file='.$file.'">'.__('Ignore this file').'</a>';
                                                    } else {
                                                        echo ' <a href="admin.php?page=madeit_security_scan&changes='.$plugin.'&version='.$version.'&deignore='.$nonce.'&file='.$file.'">'.__('Stop ignoring this file').'</a>';
                                                    }
                                                    echo '<br>';
                                                } ?>
                                                <br>
                                                <br>
                                                <?php
                                                echo '<a href="admin.php?page=madeit_security_scan&changes='.$plugin.'&version='.$version.'&ignore_all='.$nonce.'">'.__('Ignore all the files').'</a>'; ?>
                                                    
                                            </div>
                                        </div>
                                    </div>
                                <?php
        } else {
            ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('No changed files found.', 'wp-security-by-made-it')); ?>
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
                $('#repo-scan-time-ago').html('<?php printf(__('Last scan %s ago.', 'wp-security-by-made-it'), '1s'); ?>');
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
                $('#update-scan-time-ago').html('<?php printf(__('Last scan %s ago.', 'wp-security-by-made-it'), '1s'); ?>');
                $('#update-scan-core-status').html(response.core);
                $('#update-scan-plugins-status').html(response.plugin);
                $('#update-scan-themes-status').html(response.theme);
            }, 'json');
        });
    });
</script>