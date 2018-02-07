<?php

class WP_MadeIT_Security_LimitLogin
{
    private $defaultSettings = [];
    private $settings;
    private $db;

    private $attempts_delay_time;
    private $attempts_failed;
    private $attempts_block_time;
    private $attempts_block_wrong_user;
    private $attempts_block_wrong_user_count;
    private $ip;

    private $block;

    public function __construct($settings, $db)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
        $this->db = $db;

        $this->attempts_delay_time = $this->defaultSettings['firewall']['login']['attempts_delay_time'];
        $this->attempts_failed = $this->defaultSettings['firewall']['login']['attempts_failed'];
        $this->attempts_block_time = $this->defaultSettings['firewall']['login']['attempts_block_time'];
        $this->attempts_block_wrong_user = $this->defaultSettings['firewall']['login']['attempts_block_wrong_user'];
        $this->attempts_block_wrong_user_count = $this->defaultSettings['firewall']['login']['attempts_block_wrong_user_count'];

        $this->ip = $this->getIp();

        require_once MADEIT_SECURITY_DIR.'/inc/firewall/WP_MadeIT_Security_Block.php';
        $this->block = new WP_MadeIT_Security_Block($db);
    }

    public function limit_login_failed($username)
    {
        //Find login attempts
        $loginAttemptCount = 0;
        $maxLoginAttempts = 5;
        if ($loginAttemptCount <= $maxLoginAttempts) {
            $loginAttemptCount++;
        //Insert attempt
        } else {
            //Block user
        }
    }

    public function limit_login_admin_init()
    {
        if (is_user_logged_in()) {
            //reset attempts
        }
    }

    public function limit_login_errors($error)
    {
        //$error = "<strong>Login Failed</strong>: Sorry..! Wrong information..!  </br>";
        return $error;
    }

    public function limit_login_auth_signon($user, $username, $password)
    {
        if (empty($username) || empty($password)) {
            //do_action( 'wp_login_failed' );
            return $user;
        }

        $failedAttemptsDB = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_login_attempts WHERE login_failed = 1 AND ipaddress = %s AND created_at >= %d', $this->ip, time() - $this->attempts_delay_time);
        $failedAttempts = isset($failedAttemptsDB['aantal']) ? $failedAttemptsDB['aantal'] : 0;

        $blockedDB = $this->db->querySingleRecord('SELECT * FROM '.$this->db->prefix().'madeit_sec_blockip WHERE ipaddress = %s AND start_block >= %d', $this->ip, time() - $this->attempts_block_time);
        if (isset($blockedDB['id'])) {
            return new WP_Error('blocked_to_many_failed', 'To many failed logins.');
        }
        if ($failedAttempts >= $this->attempts_failed) {
            if (!isset($blockedDB['id'])) {
                //Insert block
                $this->block->createBlock($this->ip, $this->attempts_block_time, 3);
            }

            return new WP_Error('blocked_to_many_failed', 'To many failed logins.');
        }

        //CHeck if username exists else block user after ... attempts

        if ($user == null) {
            //User not authenticated
        } elseif ($user instanceof WP_Error) {
            //Authentication failed
            $err_codes = $user->get_error_codes();
            $errorNr = 0;
            if (in_array('invalid_username', $err_codes)) {
                $errorNr = 1;
            }

            if (in_array('incorrect_password', $err_codes)) {
                $errorNr = 2;
            }

            $this->db->queryWrite('INSERT INTO '.$this->db->prefix().'madeit_sec_login_attempts (ipaddress, country, username, hash, login_failed, notify, reasonNr, reason, user_agent, created_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', $this->ip, '', $username, base64_encode($password), 1, 0, $errorNr, json_encode($err_codes), $_SERVER['HTTP_USER_AGENT'], time());
            if (++$failedAttempts >= $this->attempts_failed) {
                if (!isset($blockedDB['id'])) {
                    //Insert block
                    $this->block->createBlock($this->ip, $this->attempts_block_time, $errorNr);
                }

                return new WP_Error('blocked_to_many_failed', 'To many failed logins.');
            }
        } elseif ($user instanceof WP_User) {
            //Add record to database
            $this->db->queryWrite('INSERT INTO '.$this->db->prefix().'madeit_sec_login (ipaddress, country, user_agent, username, hash, notify, created_at) VALUES (%s, %s, %s, %s, %s, %s, %s)', $this->ip, '', $_SERVER['HTTP_USER_AGENT'], $username, base64_encode($password), 0, time());
        }

        return $user;
    }

    public function getIp()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = 'UNKNOWN';
        }

        return $ip;
    }

    public function addHooks()
    {
        //add_action('wp_login_failed', [$this, 'limit_login_failed']);
        add_action('login_errors', [$this, 'limit_login_errors']);
        add_filter('authenticate', [$this, 'limit_login_auth_signon'], 30, 3);
        //add_action('admin_init', [$this, 'limit_login_admin_init']);
    }
}
