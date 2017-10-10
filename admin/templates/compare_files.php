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
        
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php printf(esc_html(__('Compare file %s of %s', 'wp-security-by-made-it')), $file, $plugin); ?>
                        </h4>
                        <div class="card-text">
                            <?php if ($error == null) {
    ?>
                                <div class="madeit-row" style="width: 100%">
                                    <div class="madeit-col">
                                        <?php
                                        echo $diff->Render($renderer); ?>
                                    </div>
                                </div>
                                <div class="madeit-row">
                                    <div class="madeit-col">
                                        <?php
                                        if (!$this->isFileIgnored($plugin, $file)) {
                                            echo ' <a href="admin.php?page=madeit_security_scan&changes='.$plugin.'&version='.$version.'&ignore='.$nonce.'&file='.$file.'">'.__('Ignore this file').'</a>';
                                        } else {
                                            echo ' <a href="admin.php?page=madeit_security_scan&changes='.$plugin.'&version='.$version.'&deignore='.$nonce.'&file='.$file.'">'.__('Stop ignoring this file').'</a>';
                                        }
    echo ' / <a href="admin.php?page=madeit_security_scan&changes='.$plugin.'&version='.$version.'&replace='.$nonceReplace.'&file='.$file.'">'.__('Replace this file with the original version.').'</a>'; ?>
                                    </div>
                                </div>
                            <?php
} else {
        ?>
                                <div class="card-text">
                                    <div class="madeit-row">
                                        <div class="madeit-col">
                                            <?php echo esc_html($error); ?>
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