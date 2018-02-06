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
                <h1><?php echo esc_html(__('Server information', 'wp-security-by-made-it')); ?></h1>
            </div>
        </div>
        
        <!-- Server information -->
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('Server information', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <table class="madeit-table">
                                    <tr>
                                        <th><?php echo __('PHP Version', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['php_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('MySQL Version', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['mysql_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('WP Version', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['wp_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Apache Version', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['apache_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('URL', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['url']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Admin URL', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['admin_url']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Users', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['user_count']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Sites', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['site_count']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Path', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['path']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('OS name', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['os_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('OS Vesion', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['os_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Memory Limit', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systemInfoResult['memory_limit']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Free disk space', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->bytesToHuman($systemInfoResult['free_disk_space'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Used disk space', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->bytesToHuman($systemInfoResult['total_disk_space'] - $systemInfoResult['free_disk_space'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Total disk space', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->bytesToHuman($systemInfoResult['total_disk_space'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Server date time', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo date('Y-m-d H:i:s'); ?> (<?php echo date('T'); ?> - <?php echo date('e'); ?>)</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cron information -->
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('Cron jobs', 'wp-security-by-made-it')); ?>
                            <small>
                                <?php
                                if (!$cronJobsInSync) {
                                    echo esc_html(__('(Cron system is not in sync)', 'wp-security-by-made-it'));
                                }
                                ?>
                            </small>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <table class="madeit-table">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Job', 'wp-security-by-made-it'); ?></th>
                                            <th><?php _e('Schedule', 'wp-security-by-made-it'); ?></th>
                                            <th><?php _e('Next run (Server time)', 'wp-security-by-made-it'); ?></th>
                                            <th><?php _e('Delete', 'wp-security-by-made-it'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody class="hover-delete">
                                        <?php
                                        $deleteNonce = wp_create_nonce('madeit_security_delete_cron');
                                        foreach ($cronjobs as $time => $crons) {
                                            foreach ($crons as $cron => $settings) {
                                                $schedule = '';
                                                foreach ($settings as $key => $setting) {
                                                    $schedule = $setting['schedule']; ?>
                                                    <tr>
                                                        <td><?php echo esc_html($cron); ?></td>
                                                        <td><?php echo esc_html($schedule); ?></td>
                                                        <td><?php echo date('Y-m-d H:i:s', $time); ?></td>
                                                        <td>
                                                            <a href="?page=madeit_security_systeminfo&delete_cron=<?php echo $deleteNonce; ?>&hook=<?php echo esc_html($cron); ?>&timestamp=<?php echo esc_html($time); ?>&key=<?php echo esc_html($key); ?>"><?php _e('Delete', 'wp-security-by-made-it'); ?></a>
                                                            <a href="?page=madeit_security_systeminfo&delete_all_cron=<?php echo $deleteNonce; ?>&hook=<?php echo esc_html($cron); ?>"><?php _e('Delete all of this', 'wp-security-by-made-it'); ?></a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- File status -->
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('File status', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <table class="madeit-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Status', 'wp-security-by-made-it'); ?></th>
                                            <th><?php echo __('Core files', 'wp-security-by-made-it'); ?></th>
                                            <th><?php echo __('Plugin files', 'wp-security-by-made-it'); ?></th>
                                            <th><?php echo __('Theme files', 'wp-security-by-made-it'); ?></th>
                                            <th><?php echo __('Other files', 'wp-security-by-made-it'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($fileStats as $status => $stats) {
                                            ?>
                                            <tr>
                                                <th><?php echo esc_html($status); ?></th>
                                                <td><?php echo esc_html($stats['core']); ?></td>
                                                <td><?php echo esc_html($stats['plugin']); ?></td>
                                                <td><?php echo esc_html($stats['theme']); ?></td>
                                                <td><?php echo esc_html($stats['other']); ?></td>
                                            </tr>
                                        <?php
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View htaccess -->
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('View htaccess', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <textarea style="width: 100%" rows="20"><?php echo file_get_contents($systemInfoResult['path'].'/.htaccess'); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View error.log -->
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('View error.log', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <textarea style="width: 100%" rows="20"><?php echo file_get_contents(WP_CONTENT_DIR.'/madeit-security-backup/error.log'); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View jobs debug info -->
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('View jobs debug info', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <table class="madeit-table">
                                    <tbody class="hover-delete">
                                        <tr>
                                            <td><?php _e('Check plugin updates', 'wp-security-by-made-it'); ?></td>
                                            <td>
                                                <?php
                                                if($this->defaultSettings['maintenance']['enable'] && $this->defaultSettings['scan']['update']) {
                                                    _e('Enabled due the maintenance mode and scan update settings.', 'wp-security-by-made-it');
                                                }
                                                elseif($this->defaultSettings['maintenance']['enable']) {
                                                    _e('Enabled due the maintenance mode.', 'wp-security-by-made-it');
                                                }
                                                elseif($this->defaultSettings['scan']['update']) {
                                                    _e('Enabled due scan update settings.', 'wp-security-by-made-it');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Check plugin updates, cronjob.', 'wp-security-by-made-it'); ?></td>
                                            <td>
                                                <?php
                                                if(wp_next_scheduled('madeit_security_check_plugin_updates') > 0) {
                                                    echo __('Next run:', 'wp-security-by-made-it') . ' ' . date('Y-m-d H:i:s', wp_next_scheduled('madeit_security_check_plugin_updates'));
                                                }
                                                else {
                                                    _e('No job planned.', 'wp-security-by-made-it');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $updateScanData = get_site_transient('madeit_security_update_scan');
                                        if(is_array($updateScanData))
                                        {
                                            ?>
                                            <tr>
                                                <td><?php _e('Last run', 'wp-security-by-made-it'); ?></td>
                                                <td>
                                                    <?php
                                                    if(isset($updateScanData['time'])) {
                                                        echo date('Y-m-d H:i:s', $updateScanData['time']);
                                                    }
                                                    else {
                                                        _e('No results found.', 'wp-security-by-made-it');
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr><td></td><td></td></tr>
                                        <tr>
                                            <td><?php _e('Scan files', 'wp-security-by-made-it'); ?></td>
                                            <td>
                                                <?php
                                                if($this->defaultSettings['scan']['repo']['core'] && $this->defaultSettings['scan']['repo']['theme'] && $this->defaultSettings['scan']['repo']['plugin']) {
                                                    _e('Enabled due the core, theme and plugin scan is enabled.', 'wp-security-by-made-it');
                                                }
                                                elseif($this->defaultSettings['scan']['repo']['core'] || $this->defaultSettings['scan']['repo']['theme'] || $this->defaultSettings['scan']['repo']['plugin']) {
                                                    _e('Enabled due the core, theme or plugin scan is enabled.', 'wp-security-by-made-it');
                                                }
                                                else {
                                                    _e('Disabled', 'wp-security-by-made-it');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Job running:', 'wp-security-by-made-it'); ?></td>
                                            <td>
                                                <?php
                                                if(wp_next_scheduled('madeit_security_loadfiles') > 0) {
                                                    echo __('Next run:', 'wp-security-by-made-it') . ' ' . date('Y-m-d H:i:s', wp_next_scheduled('madeit_security_loadfiles'));
                                                }
                                                else {
                                                    _e('No job planned.', 'wp-security-by-made-it');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Loading files job:', 'wp-security-by-made-it'); ?></td>
                                            <td>
                                                <?php
                                                if(wp_next_scheduled('madeit_security_loadfiles_run') > 0) {
                                                    echo __('Next run:', 'wp-security-by-made-it') . ' ' . date('Y-m-d H:i:s', wp_next_scheduled('madeit_security_loadfiles_run'));
                                                }
                                                else {
                                                    _e('No job planned.', 'wp-security-by-made-it');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $scanData = get_site_transient('madeit_security_scan');
                                        if(is_array($scanData))
                                        {
                                            ?>
                                            <tr>
                                                <td><?php _e('Last run started', 'wp-security-by-made-it'); ?></td>
                                                <td>
                                                    <?php
                                                    if(isset($scanData['start_time'])) {
                                                        echo date('Y-m-d H:i:s', $scanData['start_time']);
                                                    }
                                                    else {
                                                        _e('No results found.', 'wp-security-by-made-it');
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Last run time', 'wp-security-by-made-it'); ?></td>
                                                <td>
                                                    <?php
                                                    if(isset($scanData['last_com_time'])) {
                                                        echo date('Y-m-d H:i:s', $scanData['last_com_time']);
                                                    }
                                                    else {
                                                        _e('No results found.', 'wp-security-by-made-it');
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Step', 'wp-security-by-made-it'); ?></td>
                                                <td>
                                                    <?php
                                                    if(isset($scanData['step'])) {
                                                        switch($scanData['step']) {
                                                            case 0: _e('Starting scan', 'wp-security-by-made-it'); break;
                                                            case 1: _e('Loading core files', 'wp-security-by-made-it'); break;
                                                            case 2: _e('Loading plugin files', 'wp-security-by-made-it'); break;
                                                            case 3: _e('Loading theme files', 'wp-security-by-made-it'); break;
                                                            case 4: _e('Prepare to start scan', 'wp-security-by-made-it'); break;
                                                            case 5: _e('Scan core files', 'wp-security-by-made-it'); break;
                                                            case 6: _e('Scan plugin files', 'wp-security-by-made-it'); break;
                                                            case 7: _e('Scan theme files', 'wp-security-by-made-it'); break;
                                                            case 8: _e('Scan core Vulnerabilities', 'wp-security-by-made-it'); break;
                                                            case 9: _e('Scan plugin Vulnerabilities', 'wp-security-by-made-it'); break;
                                                            case 10: _e('Scan theme Vulnerabilities', 'wp-security-by-made-it'); break;
                                                            case 11: _e('Complete scan', 'wp-security-by-made-it'); break;
                                                        }
                                                    }
                                                    else {
                                                        _e('No results found.', 'wp-security-by-made-it');
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr><td></td><td></td></tr>
                                        <tr>
                                            <td><?php _e('Backup files', 'wp-security-by-made-it'); ?></td>
                                            <td>
                                                <?php
                                                $modes = [];
                                                if($this->defaultSettings['maintenance']['backup']) {
                                                    $modes[] = __('Maintenance mode', 'wp-security-by-made-it');
                                                }
                                                if($this->defaultSettings['backup']['ftp']['enabled']) {
                                                    $modes[] = __('FTP Backup', 'wp-security-by-made-it');
                                                }
                                                if($this->defaultSettings['backup']['s3']['enabled']) {
                                                    $modes[] = __('FTP Backup', 'wp-security-by-made-it');
                                                }
                                                printf(__('Enabled due: %s', 'wp-security-by-made-it'), implode($modes, ", "));
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Job running:', 'wp-security-by-made-it'); ?></td>
                                            <td>
                                                <?php
                                                if(wp_next_scheduled('madeit_security_backup') > 0) {
                                                    echo __('Next run:', 'wp-security-by-made-it') . ' ' . date('Y-m-d H:i:s', wp_next_scheduled('madeit_security_backup'));
                                                }
                                                else {
                                                    _e('No job planned.', 'wp-security-by-made-it');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Loading files job:', 'wp-security-by-made-it'); ?></td>
                                            <td>
                                                <?php
                                                if(wp_next_scheduled('madeit_security_backup_run') > 0) {
                                                    echo __('Next run:', 'wp-security-by-made-it') . ' ' . date('Y-m-d H:i:s', wp_next_scheduled('madeit_security_backup_run'));
                                                }
                                                else {
                                                    _e('No job planned.', 'wp-security-by-made-it');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $backupData = get_site_transient('madeit_security_backup');
                                        if(is_array($backupData))
                                        {
                                            ?>
                                            <tr>
                                                <td><?php _e('Last run started', 'wp-security-by-made-it'); ?></td>
                                                <td>
                                                    <?php
                                                    if(isset($backupData['start_time'])) {
                                                        echo date('Y-m-d H:i:s', $backupData['start_time']);
                                                    }
                                                    else {
                                                        _e('No results found.', 'wp-security-by-made-it');
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Last run time', 'wp-security-by-made-it'); ?></td>
                                                <td>
                                                    <?php
                                                    if(isset($backupData['last_com_time'])) {
                                                        echo date('Y-m-d H:i:s', $backupData['last_com_time']);
                                                    }
                                                    else {
                                                        _e('No results found.', 'wp-security-by-made-it');
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Step', 'wp-security-by-made-it'); ?></td>
                                                <td>
                                                    <?php
                                                    if(isset($backupData['step'])) {
                                                        switch($backupData['step']) {
                                                            case 0: _e('Starting backup', 'wp-security-by-made-it'); break;
                                                            case 1: _e('Backing up files.', 'wp-security-by-made-it'); break;
                                                            case 2: _e('Backking up database', 'wp-security-by-made-it'); break;
                                                            case 3: _e('Creating zip file', 'wp-security-by-made-it'); break;
                                                            case 4: _e('Upload to Made I.T.', 'wp-security-by-made-it'); break;
                                                            case 5: _e('Upload to FTP server', 'wp-security-by-made-it'); break;
                                                            case 6: _e('Upload to S3', 'wp-security-by-made-it'); break;
                                                            case 7: _e('Complete backup', 'wp-security-by-made-it'); break;
                                                        }
                                                    }
                                                    else {
                                                        _e('No results found.', 'wp-security-by-made-it');
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View php info -->
        <div class="madeit-row" style="margin-top: 20px;">
            <div class="madeit-col">
                <div class="madeit-card">
                    <div class="madeit-card-body">
                        <h4 class="madeit-card-title">
                            <?php echo esc_html(__('PHP Info', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <?php
                                ob_start();
                                phpinfo(INFO_ALL);
                                $out = ob_get_clean();
                                $out = str_replace('class="center"', 'style="width: 100%"', $out);
                                $out = str_replace('width="600"', 'width="900"', $out);
                                $out = str_replace('<table>', '<table class="madeit-table">', $out);
                                $out = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $out);
                                $out = preg_replace('/<a [^>]+>/', '', $out);
                                $out = preg_replace('/<\/a>/', '', $out);
                                $out = preg_replace('/<title>[^<]*<\/title>/', '', $out);
                                echo $out;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>