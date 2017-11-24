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
                <h1><?php echo esc_html(__('All issues', 'wp-security-by-made-it')); ?></h1>
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
        }
        if (isset($fileDeletedSuccesfull)) {
            ?>
            <div class="updated">
                <p>
                    <strong>
                        <?php
                        printf(__('The file %s is deleted from the server.', 'wp-security-by-made-it'), $file); ?>
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
                            <?php printf(esc_html(__('All issues of %s', 'wp-security-by-made-it')), $plugin); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <?php if (count($issues) > 0) {
            ?>
                                    <div class="card-text" style="margin-top: 20px; margin-bottom: 20px; width: 100%">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php foreach($issues as $issue) { ?>
                                                    <?php $pluginData = $this->getPluginInfoByFile($issue['filename_md5']); ?>
                                                    <div class="madeit-row" style="border-bottom: 1px solid #DDD; margin-left: 15px; margin-right: 15px; padding-bottom: 10px">
                                                        <h3 style="margin-bottom: 0; width: 100%; padding-left: 10px"><?php echo esc_html($issue['shortMsg']); ?> <small><?php echo sprintf(__('Issue created at %s', 'wp-security-by-made-it'), date('Y-m-d H:i:s', $issue['issue_created'])); ?></small></h3>
                                                        <div class="madeit-col">
                                                            <?php echo esc_html(__('Severity:', 'wp-security-by-made-it')); ?> <?php echo esc_html($this->getSeverityTxt($issue['severity'])); ?><br>
                                                            <?php echo esc_html(__('Plugin:', 'wp-security-by-made-it')); ?> <?php echo esc_html($pluginData['plugin_data']['name']); ?><br>
                                                            <?php echo esc_html($issue['longMsg']); ?><br>
                                                            <?php if(in_array($issue['type'], [2, 3])) { ?>
                                                                <a href="admin.php?page=madeit_security_scan&changes=<?php echo $pluginData['plugin']; ?>&version=<?php echo $pluginData['version']; ?>&file=<?php echo $issue['filename_md5']; ?>"><?php echo esc_html(__('Compare file', 'wp-security-by-made-it')); ?></a>
                                                            <?php } ?>
                                                            <?php if(in_array($issue['type'], [5, 2, 3])) { ?>
                                                                <a href="admin.php?page=madeit_security_scan&changes=<?php echo $pluginData['plugin']; ?>&version=<?php echo $pluginData['version']; ?>&replace=<?php echo $nonceReplace; ?>&file=<?php echo $issue['filename_md5']; ?>"><?php echo esc_html(__('Restore file', 'wp-security-by-made-it')); ?></a>
                                                            <?php } ?>
                                                            <?php if(in_array($issue['type'], [6])) { ?>
                                                                <a href="admin.php?page=madeit_security_scan&changes=<?php echo $pluginData['plugin']; ?>&version=<?php echo $pluginData['version']; ?>&delete=<?php echo $nonceDelete; ?>&file=<?php echo $issue['filename_md5']; ?>"><?php echo esc_html(__('Delete file', 'wp-security-by-made-it')); ?></a>
                                                            <?php } ?>
                                                            <?php /*<a href="admin.php?page=madeit_security_scan&fix-issue=<?php echo $issue['id']; ?>"><?php echo esc_html(__('Fix issue', 'wp-security-by-made-it')); ?></a>
                                                            <a href="admin.php?page=madeit_security_scan&ignore-issue=<?php echo $issue['id']; ?>"><?php echo esc_html(__('Ignore issue', 'wp-security-by-made-it')); ?></a>
                                                            <?php if($issue['issue_readed'] == null) { ?>
                                                                <a href="admin.php?page=madeit_security_scan&read-issue=<?php echo $issue['id']; ?>"><?php echo esc_html(__('Read issue', 'wp-security-by-made-it')); ?></a>
                                                            <?php } */ ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
        } else {
            ?>
                                    <div class="card-text">
                                        <div class="madeit-row">
                                            <div class="madeit-col">
                                                <?php echo esc_html(__('No issues files found.', 'wp-security-by-made-it')); ?>
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