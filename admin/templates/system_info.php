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
                                            <th><?php echo __('Job', 'wp-security-by-made-it'); ?></th>
                                            <th><?php echo __('Schedule', 'wp-security-by-made-it'); ?></th>
                                            <th><?php echo __('Next run (Server time)', 'wp-security-by-made-it'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cronjobs as $time => $crons) {
                                    foreach ($crons as $cron => $settings) {
                                        $schedule = '';
                                        foreach ($settings as $setting) {
                                            $schedule = $setting['schedule'];
                                        } ?>
                                                <tr>
                                                    <td><?php echo esc_html($cron); ?></td>
                                                    <td><?php echo esc_html($schedule); ?></td>
                                                    <td><?php echo date('Y-m-d H:i:s', $time); ?></td>
                                                </tr>
                                            <?php
                                    }
                                } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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