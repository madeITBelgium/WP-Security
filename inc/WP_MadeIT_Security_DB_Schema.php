<?php

class WP_MadeIT_Security_DB_Schema
{
    private $db;
    private $schema = [
        'madeit_sec_filelist' => [ //Columns
            'filename_md5' => 'varchar(64) NOT NULL PRIMARY KEY',
            'filename' => 'varchar(1000) NOT NULL',
            'old_md5' => 'varchar(64) NULL',
            'new_md5' => 'varchar(64) NULL',
            'file_created' => 'int UNSIGNED NOT NULL',
            'file_changed' => 'int UNSIGNED NULL',
            'file_checked' => 'int UNSIGNED NULL',
            'file_loaded' => 'int UNSIGNED NULL',
            'file_deleted' => 'int UNSIGNED NULL',
            
            'exist_in_orig' => 'tinyint UNSIGNED NOT NULL default 1',
            'changed' => 'tinyint UNSIGNED NOT NULL default 0',
            'is_safe' => 'tinyint UNSIGNED NOT NULL default 1',
            'ignore' => 'tinyint UNSIGNED NOT NULL default 0',
            'reason' => 'varchar(64) NULL',
            
            'need_backup' => 'tinyint UNSIGNED NOT NULL default 0',
            'in_backup' => 'tinyint UNSIGNED NOT NULL default 0',
            'has_url' => 'tinyint UNSIGNED NOT NULL default 0',
            'safe_url' => 'tinyint UNSIGNED NOT NULL default 0',
            
            'plugin_theme' => 'varchar(255) NULL',
            'core_file' => 'tinyint UNSIGNED NOT NULL default 0',
            'plugin_file' => 'tinyint UNSIGNED NOT NULL default 0',
            'theme_file' => 'tinyint UNSIGNED NOT NULL default 0',
            'content_file' => 'tinyint UNSIGNED NOT NULL default 0',
        ]
    ];
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function loadDB() {
        foreach($this->schema as $table => $columns) {
            if(!$this->db->tableExists($table)) {
                
                $query = "create table IF NOT EXISTS " . $this->db->prefix() . $table . " (";
                
                $i = 1;
                foreach($columns as $column => $creator) {
                    $lastColumn = $i == count($columns);
                    $query .= "`" . $column . "` " . $creator . (!$lastColumn ? ", " : "");
                    $i++;
                }
                
                $query .= ")";
                $this->db->queryWrite($query);
            }
        }
    }
    
}
