<?php

class WP_MadeIT_Security_DB_Schema
{
    private $db;
    private $schema = [
        'madeit_sec_filelist' => [ //Columns
            'filename_md5' => 'varchar(64) NOT NULL PRIMARY KEY',
            'filename'     => 'varchar(1000) NOT NULL',
            'old_md5'      => 'varchar(64) NULL',
            'new_md5'      => 'varchar(64) NULL',
            'file_created' => 'int UNSIGNED NOT NULL',
            'file_changed' => 'int UNSIGNED NULL',
            'file_checked' => 'int UNSIGNED NULL',
            'file_loaded'  => 'int UNSIGNED NULL',
            'file_deleted' => 'int UNSIGNED NULL',

            'exist_in_orig' => 'tinyint UNSIGNED NOT NULL default 1',
            'changed'       => 'tinyint UNSIGNED NOT NULL default 0',
            'is_safe'       => 'tinyint UNSIGNED NOT NULL default 1',
            'ignore'        => 'tinyint UNSIGNED NOT NULL default 0',
            'reason'        => 'varchar(64) NULL',

            'need_backup' => 'tinyint UNSIGNED NOT NULL default 0',
            'in_backup'   => 'tinyint UNSIGNED NOT NULL default 0',
            'has_url'     => 'tinyint UNSIGNED NOT NULL default 0',
            'safe_url'    => 'tinyint UNSIGNED NOT NULL default 0',

            'plugin_theme' => 'varchar(255) NULL',
            'core_file'    => 'tinyint UNSIGNED NOT NULL default 0',
            'plugin_file'  => 'tinyint UNSIGNED NOT NULL default 0',
            'theme_file'   => 'tinyint UNSIGNED NOT NULL default 0',
            'content_file' => 'tinyint UNSIGNED NOT NULL default 0',
        ],
        'madeit_sec_issues' => [ //Columns
            'id'            => 'int UNSIGNED NOT NULL auto_increment PRIMARY KEY',
            'filename_md5'  => 'varchar(64) NULL',
            'filename'      => 'varchar(1000) NULL',
            'old_md5'       => 'varchar(64) NULL',
            'new_md5'       => 'varchar(64) NULL',
            'type'          => 'int UNSIGNED NOT NULL default 0', //1 = File change, 2 = File deleted, 3 = File added
            'severity'      => 'tinyint UNSIGNED NOT NULL default 0', //1 = trivial, 2 => minor 3 => major, 4 => critical, 5 => blocked
            'issue_created' => 'int UNSIGNED NULL',
            'issue_fixed'   => 'int UNSIGNED NULL',
            'issue_ignored' => 'int UNSIGNED NULL',
            'issue_readed'  => 'int UNSIGNED NULL',
            'issue_remind'  => 'int UNSIGNED NULL',
	        'shortMsg'      => 'varchar(255) NULL',
	        'longMsg'       => 'text NULL',
	        'data'          => 'text NULL',
        ],
    ];

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function loadDB()
    {
        foreach ($this->schema as $table => $columns) {
            if (!$this->db->tableExists($table)) {
                $query = 'create table IF NOT EXISTS '.$this->db->prefix().$table.' (';

                $i = 1;
                foreach ($columns as $column => $creator) {
                    $lastColumn = $i == count($columns);
                    $query .= '`'.$column.'` '.$creator.(!$lastColumn ? ', ' : '');
                    $i++;
                }

                $query .= ')';
                $this->db->queryWrite($query);
            }
        }
    }
}
