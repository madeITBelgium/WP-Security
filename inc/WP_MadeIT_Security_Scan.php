<?php

class WP_MadeIT_Security_Scan
{
    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function arrayDirectory($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (is_dir($directory.'/'.$file)) {
                    $files[$file] = $this->arrayDirectory($directory.'/'.$file);
                } else {
                    $files[$file] = $file;
                }
            }
        }

        $dir->close();

        return $files;
    }

    public function hashDirectory($directory, $exclude = [])
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (!in_array($file, $exclude)) {
                    if (is_dir($directory.'/'.$file)) {
                        $files[] = $this->hashDirectory($directory.'/'.$file);
                    } else {
                        $files[] = md5_file($directory.'/'.$file);
                    }
                }
            }
        }

        $dir->close();

        return md5(implode('', $files));
    }

    public function fileHashDirectory($directory, $exclude = [])
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = [];
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (!in_array($file, $exclude)) {
                    if (is_dir($directory.'/'.$file)) {
                        $files[$file] = $this->fileHashDirectory($directory.'/'.$file);
                    } else {
                        $files[$file] = md5_file($directory.'/'.$file);
                    }
                }
            }
        }

        $dir->close();

        return $files;
    }
}
