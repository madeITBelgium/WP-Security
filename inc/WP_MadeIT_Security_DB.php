<?php

class WP_MadeIT_Security_DB
{
    public $errorMsg = false;

    public function __construct()
    {
        require_once MADEIT_SECURITY_DIR.'/inc/WP_MadeIT_Security_DB_Schema.php';
        $schema = new WP_MadeIT_Security_DB_Schema($this);
        $schema->loadDB();
    }

    public function querySingle()
    {
        global $wpdb;
        if (func_num_args() > 1) {
            $args = func_get_args();

            return $wpdb->get_var(call_user_func_array([$wpdb, 'prepare'], $args));
        } else {
            return $wpdb->get_var(func_get_arg(0));
        }
    }

    public function querySingleRecord()
    { //queryInSprintfFormat, arg1, arg2, ... :: Returns a single assoc-array or null if nothing found.
        global $wpdb;
        if (func_num_args() > 1) {
            $args = func_get_args();

            return $wpdb->get_row(call_user_func_array([$wpdb, 'prepare'], $args), ARRAY_A);
        } else {
            return $wpdb->get_row(func_get_arg(0), ARRAY_A);
        }
    }

    public function queryWrite()
    {
        global $wpdb;
        if (func_num_args() > 1) {
            $args = func_get_args();

            return $wpdb->query(call_user_func_array([$wpdb, 'prepare'], $args));
        } else {
            return $wpdb->query(func_get_arg(0));
        }
    }

    public function flush()
    { //Clear cache
        global $wpdb;
        $wpdb->flush();
    }

    public function querySelect()
    { //sprintfString, arguments :: always returns array() and will be empty if no results.
        global $wpdb;
        if (func_num_args() > 1) {
            $args = func_get_args();

            return $wpdb->get_results(call_user_func_array([$wpdb, 'prepare'], $args), ARRAY_A);
        } else {
            return $wpdb->get_results(func_get_arg(0), ARRAY_A);
        }
    }

    public function queryWriteIgnoreError()
    { //sprintfString, arguments
        global $wpdb;
        $oldSuppress = $wpdb->suppress_errors(true);
        $args = func_get_args();
        call_user_func_array([$this, 'queryWrite'], $args);
        $wpdb->suppress_errors($oldSuppress);
    }

    public function columnExists($table, $col)
    {
        global $wpdb;
        $prefix = $wpdb->base_prefix;
        $table = $prefix.$table;
        $q = $this->querySelect("desc $table");
        foreach ($q as $row) {
            if ($row['Field'] == $col) {
                return true;
            }
        }

        return false;
    }

    public function dropColumn($table, $col)
    {
        global $wpdb;
        $prefix = $wpdb->base_prefix;
        $table = $prefix.$table;
        $this->queryWrite("alter table $table drop column $col");
    }

    public function createKeyIfNotExists($table, $col, $keyName)
    {
        $table = $this->prefix().$table;
        $exists = $this->querySingle("show tables like '$table'");
        $keyFound = false;
        if ($exists) {
            $q = $this->querySelect("show keys from $table");
            foreach ($q as $row) {
                if ($row['Key_name'] == $keyName) {
                    $keyFound = true;
                }
            }
        }
        if (!$keyFound) {
            $this->queryWrite("alter table $table add KEY $keyName($col)");
        }
    }

    public function prefix()
    {
        global $wpdb;

        return $wpdb->base_prefix;
    }

    public function truncate($table)
    { //Ensures everything is deleted if user is using MySQL >= 5.1.16 and does not have "drop" privileges
        $this->queryWrite("truncate table $table");
        $this->queryWrite("delete from $table");
    }

    public function getLastError()
    {
        global $wpdb;

        return $wpdb->last_error;
    }

    public function realEscape($str)
    {
        global $wpdb;

        return $wpdb->_real_escape($str);
    }

    public function tableExists($table)
    {
        global $wpdb;
        $prefix = $wpdb->base_prefix;
        $table = $prefix.$table;

        return $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
    }
}
