<?php

class WP_MadeIT_Security_Issue
{
    public $errorMsg = false;

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }
    
    private function generateShortMessage($type, $filename)
    {
        //1 = File change, 2 = File different then repo, 3 = File infected, 4 = File deleted, 5 = File deleted, 6 = File is added
        if($type == 1) {
            $shortMsg = sprintf(__('File changed', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 2) {
            $shortMsg = sprintf(__('File changed', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 3) {
            $shortMsg = sprintf(__('File infected', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 4) {
            $shortMsg = sprintf(__('File deleted', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 5) {
            $shortMsg = sprintf(__('File deleted', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 6) {
            $shortMsg = sprintf(__('New file added', 'wp-security-by-made-it'), $filename);
        }
        return $shortMsg;
    }
    
    private function generateLongMessage($type, $filename)
    {
        //1 = File change, 2 = File different then repo, 3 = File infected, 4 = File deleted, 5 = File deleted, 6 = File is added
        if($type == 1) {
            $longMsg = sprintf(__('The file %s is changed.', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 2) {
            $longMsg = sprintf(__('The file %s is different from the original.', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 3) {
            $longMsg = sprintf(__('The file %s is infected.', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 4) {
            $longMsg = sprintf(__('The file %s is deleted from you installation.', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 5) {
            $longMsg = sprintf(__('The file %s is deleted from you installation. But it exist in the original version.', 'wp-security-by-made-it'), $filename);
        }
        elseif($type == 6) {
            $longMsg = sprintf(__('The file %s is added to your installation.', 'wp-security-by-made-it'), $filename);
        }
        return $longMsg;
    }

    public function createIssue($filename_md5, $filename, $oldMd5, $newMd5, $type, $severity, $data = [])
    {
        $shortMsg = $this->generateShortMessage($type, $filename);
        $longMsg = $this->generateLongMessage($type, $filename);
        
        $issues = $this->db->querySingleRecord('SELECT count(*) as aantal FROM '.$this->db->prefix().'madeit_sec_issues WHERE issue_fixed IS NULL AND filename_md5 = %s', $filename_md5);
        if(!isset($issues['aantal']) || (isset($issues['aantal']) && $issues['aantal'] == 0)) {
            $this->db->queryWrite('INSERT INTO '.$this->db->prefix().'madeit_sec_issues (filename_md5, filename, old_md5, new_md5, type, severity, issue_created, shortMsg, longMsg, data) VALUES (%s, %s, %s, %s,%s, %s, %s, %s, %s, %s)', 
                                  $filename_md5, $filename, $oldMd5, $newMd5, $type, $severity, time(), $shortMsg, $longMsg, json_encode($data));
        }
    }
    
    public function updateIssue($filename_md5, $type, $severity, $data = [])
    {
        $issue = $this->db->querySingleRecord('SELECT * FROM '.$this->db->prefix().'madeit_sec_issues WHERE issue_fixed IS NULL AND filename_md5 = %s', $filename_md5);
        if($issue != null) {
            $shortMsg = $this->generateShortMessage($type, $issue['filename']);
            $longMsg = $this->generateLongMessage($type, $issue['filename']);
            
            $this->db->queryWrite('UPDATE '.$this->db->prefix().'madeit_sec_issues SET type = %s, severity = %s, shortMsg = %s, longMsg = %s, data = %s, issue_created = %s, issue_fixed = NULL, issue_ignored = NULL, issue_readed = NULL, issue_remind = NULL WHERE id = %s', 
                $type, $severity, $shortMsg, $longMsg, json_encode($data), time(), $issue['id']);
        }
    }
}
