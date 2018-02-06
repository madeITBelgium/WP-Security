<?php

class WP_MadeIT_Security_Update
{
    private $defaultSettings = [];
    private $settings;
    private $db;
    private $issues;

    public function __construct($settings, $db)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
        $this->db = $db;
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Issue.php';
        $this->issues = new WP_MadeIT_Security_Issue($db);
    }

    public function activateSechduler($deactivate)
    {
        if ($deactivate) {
            wp_clear_scheduled_hook('madeit_security_check_plugin_updates');
        } else {
            if (false === wp_next_scheduled('madeit_security_check_plugin_updates')) {
                wp_schedule_event(time(), 'hourly', 'madeit_security_check_plugin_updates');
            }
        }
    }

    public function check_plugin_updates()
    {
        if ($this->defaultSettings['maintenance']['enable']) {
            if (strlen($this->defaultSettings['maintenance']['key']) > 0) {
                $sendRequestToMadeIT = $this->postInfoToMadeIT($this->defaultSettings['maintenance']['key'], $this->getWebsiteInfo());
                $json = json_decode($sendRequestToMadeIT, true);
                if ((isset($json['success']) && $json['success'] !== true) || !isset($json['success'])) {
                    //Log error
                }
            }
        } elseif ($this->defaultSettings['scan']['update']) {
            $this->getWebsiteInfo();
        }
    }

    private function postInfoToMadeIT($key, $info)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/website/'.$key);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['info' => json_encode($info)]));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return $server_output;
    }

    public function getWebsiteInfo()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Plugin.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Core.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_Theme.php';
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_SystemInfo.php';

        $pluginsC = new WP_MadeIT_Security_Plugin();
        $coreC = new WP_MadeIT_Security_Core();
        $themesC = new WP_MadeIT_Security_Theme();
        $systemInfo = new WP_MadeIT_Security_SystemInfo();

        //Info
        $info = $systemInfo->getSystemInfo();

        //Plugins
        $plugins = $pluginsC->getPlugins(true);

        //Themes
        $themes = $themesC->getThemes(true);

        //core
        $latest_core_version = $coreC->getLatestWPVersion();

        $updateCounts = [
            'core'          => $coreC->hasUpdate() ? 1 : 0,
            'theme'         => $themesC->countUpdates(false),
            'plugin'        => $pluginsC->countUpdates(false),
            'time'          => time(),
            'active_plugin' => $pluginsC->getActivePlugins(),
        ];

        $oldData = get_site_transient('madeit_security_update_scan');
        if (isset($oldData['active_plugin'])) {
            $this->checkActivePlugins($oldData['active_plugin'], $updateCounts['active_plugin']);
        }

        set_site_transient('madeit_security_update_scan', $updateCounts);

        $result = [
            'latest_core_version' => $latest_core_version,
            'info'                => $info,
            'plugins'             => $plugins,
            'themes'              => $themes,
        ];

        return $result;
    }

    private function checkActivePlugins($oldActivePlugins, $newActivePlugins)
    {
        $disabledPlugins = [];
        $activatedPlugins = [];

        $pluginNames = [];
        foreach ($oldActivePlugins as $plugin) {
            $pluginNames[] = $plugin['plugin'];
        }

        foreach ($newActivePlugins as $plugin) {
            if (in_array($plugin['plugin'], $pluginNames)) {
                //Plugin is still active
                $key = array_search($plugin['plugin'], $pluginNames);
                unset($pluginNames[$key]);
                unset($oldActivePlugins[$key]);
            } else {
                //Plugin is activated
                $this->notifyActivatedPlugin($plugin);
            }
        }

        //pluginNames plugins are deactivated
        if (count($oldActivePlugins) > 0) {
            foreach ($oldActivePlugins as $plugin) {
                $this->notifyDeactivatedPlugin($plugin);
            }
        }
    }

    private function notifyActivatedPlugin($plugin)
    {
        $this->issues->createIssue(md5($plugin['name'].time()), $plugin['name'], null, null, 10, 3);
    }

    private function notifyDeactivatedPlugin($plugin)
    {
        $this->issues->createIssue(md5($plugin['name'].time()), $plugin['name'], null, null, 11, 4);
    }

    public function addHooks()
    {
        add_action('madeit_security_check_plugin_updates', [$this, 'check_plugin_updates']);
        add_action('upgrader_process_complete', [$this, 'check_plugin_updates']);

        if ($this->defaultSettings['maintenance']['enable'] || $this->defaultSettings['scan']['update']) {
            $this->activateSechduler(false);
        } else {
            $this->activateSechduler(true);
        }
    }
}
