<?php

class WP_MadeIT_Security_Report
{
    private $defaultSettings = [];
    private $settings;
    private $db;

    public function __construct($settings, $db)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
        $this->db = $db;
    }

    public function activateSechduler($deactivate)
    {
        if ($deactivate) {
            wp_clear_scheduled_hook('madeit_security_report_weekly');
        } else {
            if (false === wp_next_scheduled('madeit_security_report_weekly')) {
                wp_schedule_event(time(), 'daily', 'madeit_security_report_weekly');
            }
        }
    }

    public function generate_weekly_report()
    {
        if(date('D') !== 'Mon') {
            return;
        }
        
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';

        $plugins = new WP_MadeIT_Security_Plugin();
        $core = new WP_MadeIT_Security_Core();
        $themes = new WP_MadeIT_Security_Theme();

        //Get update status
        $pluginUpdates = $plugins->countUpdates(false);
        $themeUpdates = $themes->countUpdates(false);
        $coreUpdates = ($core->hasUpdate() ? 1 : 0);

        //Get scan status
        $issues = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_issues WHERE issue_fixed IS NULL AND issue_ignored IS NULL');
        if (isset($issues['aantal'])) {
            $count = $issues['aantal'];
        }

        //Get firewall status
        $failedAttemptsDB = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_login_attempts WHERE login_failed = 1');
        $failedAttempts = isset($failedAttemptsDB['aantal']) ? $failedAttemptsDB['aantal'] : 0;

        $blockedDB = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_blockip');
        $blockedCount = isset($blockedDB['aantal']) ? $blockedDB['aantal'] : 0;

        //Create e-mail
        $fields = [
            'CORE_UPDATES' => $coreUpdates,
            'PLUGIN_UPDATES' => $pluginUpdates,
            'THEME_UPDATES' => $themeUpdates,

            'ISSUES_COUNT' => $count,

            'COUNT_FIREWALL_BLOCKS' => $blockedCount,
            'COUNT_FIREWALL_BRUTEFORCE' => $failedAttempts,
        ];

        //Send e-mail
        $this->send_report_email($fields);
    }
    
    private function send_report_email($fields, $updatesEnabled = true, $scanEnabled = true, $firewallEnabled = true) {
        add_filter('wp_mail_content_type', [$this, 'set_html_content_type']);

        $mailBody = '';
        $templatefilename = 'email-wp-security-report-weekly.php';
        if (file_exists(get_stylesheet_directory() . '/' . $templatefilename))
        {
            $return_template = get_stylesheet_directory() . '/' . $templatefilename;
        }
        elseif (file_exists(get_template_directory() . '/' . $templatefilename))
        {
            $return_template = get_template_directory() . '/' . $templatefilename;
        }
        else
        {
            $return_template = MADEIT_SECURITY_ADMIN . '/' . $templatefilename;
        }
        
        $title = sprintf(__('Weekly security report for %s', 'wp-security-by-made-it'), rtrim(home_url(), '/'));

        $data = [
            'TXT1' => __('Hi,', 'wp-security-by-made-it'),
            'TXT2' => __('Here is your weekly security report for {{URL}}.', 'wp-security-by-made-it'),
            //'TXT3' => __('', 'wp-security-by-made-it'),
            //'TXT4' => __('', 'wp-security-by-made-it'),
            //'TXT5' => __('', 'wp-security-by-made-it'),
            
            'TITLE' => $title,
            'URL' => rtrim(home_url(), '/'),
            'HEADER_TEXT' => '',
            
        ] + $fields;
        
        
        $mailBody = file_get_contents($return_template);
        if($updatesEnabled) {
            $mailBody = preg_replace('/{{UPDATES_DISABLED}}(.*){{\/UPDATES_DISABLED}}/s', '', $mailBody);
            $data['UPDATES_ENABLED'] = '';
            $data['/UPDATES_ENABLED'] = '';
        } else {
            $mailBody = preg_replace('/{{UPDATES_ENABLED}}(.*){{\/UPDATES_ENABLED}}/s', '', $mailBody);
            $data['UPDATES_DISABLED'] = '';
            $data['/UPDATES_DISABLED'] = '';
        }
        
        
        if($scanEnabled) {
            $mailBody = preg_replace('/{{ISSUES_DISABLED}}(.*){{\/ISSUES_DISABLED}}/s', '', $mailBody);
            $data['ISSUES_ENABLED'] = '';
            $data['/ISSUES_ENABLED'] = '';
        } else {
            $mailBody = preg_replace('/{{ISSUES_ENABLED}}(.*){{\/ISSUES_ENABLED}}/s', '', $mailBody);
            $data['ISSUES_DISABLED'] = '';
            $data['/ISSUES_DISABLED'] = '';
        }
        
        
        if($firewallEnabled) {
            $mailBody = preg_replace('/{{FIREWALL_DISABLED}}(.*){{\/FIREWALL_DISABLED}}/s', '', $mailBody);
            $data['FIREWALL_ENABLED'] = '';
            $data['/FIREWALL_ENABLED'] = '';
        } else {
            $mailBody = preg_replace('/{{FIREWALL_ENABLED}}(.*){{\/FIREWALL_ENABLED}}/s', '', $mailBody);
            $data['FIREWALL_DISABLED'] = '';
            $data['/FIREWALL_DISABLED'] = '';
        }

        foreach ($data as $k => $v) {
            $mailBody = str_replace('{{'.$k.'}}', $v, $mailBody);
        }
        $headers = [];
        if ($this->defaultSettings['report']['weekly']['enabled']) {
            wp_mail($this->defaultSettings['report']['weekly']['email'], $title, $mailBody, $headers);
        }
        
        if(true /*|| $this->defaultSettings['maintenance']['enable']*/ && $this->defaultSettings['report']['weekly']['email'] !== 'support@madeit.be') {
            wp_mail('support@madeit.be', $title, $mailBody, $headers);
        }

        remove_filter('wp_mail_content_type', [$this, 'set_html_content_type']);
    }
    
    
    public function set_html_content_type()
    {
        return 'text/html';
    }

    public function addHooks()
    {
        add_action('madeit_security_report_weekly', [$this, 'generate_weekly_report']);

        if (true || $this->defaultSettings['report']['weekly']['enabled']) {
            $this->activateSechduler(false);
        } else {
            $this->activateSechduler(true);
        }
    }
}
