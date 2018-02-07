<?php

class WP_MadeIT_Security_Block
{
    private $db;

    public function __construct($db = null)
    {
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
        $this->db->queryWrite("INSERT INTO " . $this->db->prefix() . "madeit_sec_blockip (ipaddress, country, start_block, end_block, blocked, reasonNr, reason, notify, created_at) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", 
                              $ipaddress, '', time(), time() + $timeToBlock, 1, 1, $shortMsg, 0, time()); 
    }
}
