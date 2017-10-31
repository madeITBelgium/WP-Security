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
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['php_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('MySQL Version', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['mysql_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('WP Version', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['wp_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Apache Version', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['apache_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('URL', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['url']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Admin URL', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['admin_url']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Users', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['user_count']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Sites', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['site_count']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Path', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['path']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('OS name', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['os_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('OS Vesion', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['os_version']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Memory Limit', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo()['memory_limit']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Free disk space', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo(true)['free_disk_space']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Used disk space', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->bytesToHuman($systeminfo->getSystemInfo()['total_disk_space'] - $systeminfo->getSystemInfo()['free_disk_space'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo __('Total disk space', 'wp-security-by-made-it'); ?></th>
                                        <td><?php echo esc_html($systeminfo->getSystemInfo(true)['total_disk_space']); ?></td>
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
                                        <?php foreach (_get_cron_array() as $time => $crons) {
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
                            <?php echo esc_html(__('View htaccess', 'wp-security-by-made-it')); ?>
                        </h4>
                        <div class="card-text">
                            <div class="madeit-row">
                                <textarea style="width: 100%" rows="20"><?php echo file_get_contents($systeminfo->getSystemInfo()['path'].'/.htaccess'); ?></textarea>
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