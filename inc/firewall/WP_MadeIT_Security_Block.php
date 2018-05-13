<?php

class WP_MadeIT_Security_Block
{
    private $db;
    private $settings;

    public function __construct($settings, $db = null)
    {
        $this->settings = $settings;
        $this->db = $db;
    }

    private function generateShortMessage($type)
    {
        if ($type == 1) {
            $shortMsg = __('Invallid username', 'wp-security-by-made-it');
        } elseif ($type == 2) {
            $shortMsg = __('Invallid password', 'wp-security-by-made-it');
        } elseif ($type == 3) {
            $shortMsg = __('To many failed attempts', 'wp-security-by-made-it');
        }

        return $shortMsg;
    }

    public function createBlock($ipaddress, $timeToBlock, $reasonNr)
    {
        $shortMsg = $this->generateShortMessage($reasonNr);
        $this->db->queryWrite('INSERT INTO '.$this->db->prefix().'madeit_sec_blockip (ipaddress, country, start_block, end_block, blocked, reasonNr, reason, notify, created_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)',
                              $ipaddress, '', time(), time() + $timeToBlock, 1, 1, $shortMsg, 0, time());

        $this->createBlockFile();
    }

    private function createBlockFile()
    {
        $blocks = $this->db->querySelect('SELECT DISTINCT ipaddress FROM '.$this->db->prefix().'madeit_sec_blockip WHERE start_block <= %s AND (end_block >= %s OR end_block IS NULL)', time(), time());

        $result = [];
        foreach ($blocks as $subArray) {
            foreach ($subArray as $value) {
                $result[] = $value;
            }
        }

        $dir = $this->settings->createLoggingDir();
        $content = "<?php\n";
        $content .= "//WP Security By Made I.T. Blocked IPs\n";
        $content .= "\$wp_security_by_madeit_ip_blocks = array('".implode("', '", $result)."');\n";
        file_put_contents($dir.'/wp-security-blocks.php', $content);
    }
}
