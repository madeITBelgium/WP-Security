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
                            <?php printf(esc_html(__('File %s of %s', 'wp-security-by-made-it')), $fileName, $plugin); ?>
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
                                        if ($fileData['ignore'] == 0) {
                                            echo ' <a href="admin.php?page=madeit_security_scan&notexist='.$plugin.'&version='.$version.'&ignore='.$nonce.'&file='.$file.'">'.__('Ignore this file').'</a>';
                                        } else {
                                            echo ' <a href="admin.php?page=madeit_security_scan&notexist='.$plugin.'&version='.$version.'&deignore='.$nonce.'&file='.$file.'">'.__('Stop ignoring this file').'</a>';
                                        }
    echo ' / <a href="admin.php?page=madeit_security_scan&notexist='.$plugin.'&version='.$version.'&delete='.$nonceDelete.'&file='.$file.'">'.__('Delete this file.').'</a>'; ?>
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