<?php

class WP_MadeIT_Security_AutoPrependHelper
{
    private $firewallFilename = 'madeit-firewall.php';

    public function getFilesNeededForBackup($serverConfig)
    {
        $backups = [];
        $htaccess = $this->getHtaccessPath();
        switch ($serverConfig) {
            case 'apache-mod_php':
            case 'apache-suphp':
            case 'litespeed':
            case 'cgi':
                if (file_exists($htaccess)) {
                    $backups[] = $htaccess;
                }
                break;
        }
        if ($userIni = ini_get('user_ini.filename')) {
            $userIniPath = $this->getUserIniPath();
            switch ($serverConfig) {
                case 'cgi':
                case 'apache-suphp':
                case 'nginx':
                case 'litespeed':
                case 'iis':
                    if (file_exists($userIniPath)) {
                        $backups[] = $userIniPath;
                    }
                    break;
            }
        }

        return $backups;
    }

    public function downloadBackups($serverConfig, $index = 0)
    {
        $backups = $this->getFilesNeededForBackup($serverConfig);
        if ($backups && array_key_exists($index, $backups)) {
            $url = site_url();
            $url = preg_replace('/^https?:\/\//i', '', $url);
            $url = preg_replace('/[^a-zA-Z0-9\.]+/', '_', $url);
            $url = preg_replace('/^_+/', '', $url);
            $url = preg_replace('/_+$/', '', $url);
            header('Content-Type: application/octet-stream');
            $backupFileName = ltrim(basename($backups[$index]), '.');
            header('Content-Disposition: attachment; filename="'.$backupFileName.'_Backup_for_'.$url.'.txt"');
            readfile($backups[$index]);
            exit();
        }
    }

    private function getFirewallFileContent($currentAutoPrependedFile = null)
    {
        $currentAutoPrepend = '';
        if ($currentAutoPrependedFile && is_file($currentAutoPrependedFile) && !MADEIT_SECURITY_SUBDIRECTORY_INSTALL) {
            $currentAutoPrepend = sprintf('
// This file was the current value of auto_prepend_file during the Firewall installation (%2$s)
if (file_exists(%1$s)) {
	include_once %1$s;
}', var_export($currentAutoPrependedFile, true), date('r'));
        }

        return sprintf(
            '<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.
%3$s
if (file_exists(%1$s)) {
	define("MADEIT_SECURITY_LOG_PATH", %2$s);
	include_once %1$s;
}
?>',
            var_export(MADEIT_SECURITY_DIR.'/inc/firewall/WP_MadeIT_Security_Init.php', true),
            var_export(!MADEIT_SECURITY_SUBDIRECTORY_INSTALL ? WP_CONTENT_DIR.'/madeit-security-backup/' : MADEIT_SECURITY_LOG_PATH, true),
            $currentAutoPrepend
        );
    }

    public function performInstallation($serverConfig, $wp_filesystem, $currentAutoPrependedFile)
    {
        $firewallPath = ABSPATH.$this->firewallFilename;
        if (!$wp_filesystem->put_contents($firewallPath, $this->getFirewallFileContent($currentAutoPrependedFile))) {
            throw new Exception('We were unable to create the <code>madeit-firewall.php</code> file in the root of the WordPress installation. It\'s possible WordPress cannot write to the <code>madeit-firewall.php</code> file because of file permissions. Please verify the permissions are correct and retry the installation.');
        }

        $htaccessPath = $this->getHtaccessPath();
        $homePath = dirname($htaccessPath);

        $userIniPath = $this->getUserIniPath();
        $userIni = ini_get('user_ini.filename');

        $userIniHtaccessDirectives = '';
        if ($userIni) {
            $userIniHtaccessDirectives = sprintf('<Files "%s">
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
</Files>
', addcslashes($userIni, '"'));
        }

        // .htaccess configuration
        switch ($serverConfig) {
            case 'apache-mod_php':
                $autoPrependDirective = sprintf("# WP SECURITY FIREWALL
<IfModule mod_php%d.c>
	php_value auto_prepend_file '%s'
</IfModule>
$userIniHtaccessDirectives
# END WP SECURITY FIREWALL
", PHP_MAJOR_VERSION, addcslashes($firewallPath, "'"));
                break;

            case 'litespeed':
                $escapedBootstrapPath = addcslashes($firewallPath, "'");
                $autoPrependDirective = sprintf("# WP SECURITY FIREWALL
<IfModule LiteSpeed>
php_value auto_prepend_file '%s'
</IfModule>
<IfModule lsapi_module>
php_value auto_prepend_file '%s'
</IfModule>
$userIniHtaccessDirectives
# END WP SECURITY FIREWALL
", $escapedBootstrapPath, $escapedBootstrapPath);
                break;

            case 'apache-suphp':
                $autoPrependDirective = sprintf("# WP SECURITY FIREWALL
$userIniHtaccessDirectives
# END WP SECURITY FIREWALL
", addcslashes($homePath, "'"));
                break;

            case 'cgi':
                if ($userIniHtaccessDirectives) {
                    $autoPrependDirective = sprintf("# WP SECURITY FIREWALL
$userIniHtaccessDirectives
# END WP SECURITY FIREWALL
", addcslashes($homePath, "'"));
                }
                break;

        }

        if (!empty($autoPrependDirective)) {
            // Modify .htaccess
            $htaccessContent = $wp_filesystem->get_contents($htaccessPath);

            if ($htaccessContent) {
                $regex = '/# WP SECURITY FIREWALL.*?# END WP SECURITY FIREWALL/is';
                if (preg_match($regex, $htaccessContent, $matches)) {
                    $htaccessContent = preg_replace($regex, $autoPrependDirective, $htaccessContent);
                } else {
                    $htaccessContent .= "\n\n".$autoPrependDirective;
                }
            } else {
                $htaccessContent = $autoPrependDirective;
            }

            if (!$wp_filesystem->put_contents($htaccessPath, $htaccessContent)) {
                throw new Exception('We were unable to make changes to the .htaccess file. It\'s
                possible WordPress cannot write to the .htaccess file because of file permissions, which may have been
                set by another security plugin, or you may have set them manually. Please verify the permissions allow
                the web server to write to the file, and retry the installation.');
            }
            if ($serverConfig == 'litespeed') {
                // sleep(2);
                $wp_filesystem->touch($htaccessPath);
            }
        }
        if ($userIni) {
            // .user.ini configuration
            switch ($serverConfig) {
                case 'cgi':
                case 'nginx':
                case 'apache-suphp':
                case 'litespeed':
                case 'iis':
                    $autoPrependIni = sprintf("; WP SECURITY FIREWALL
auto_prepend_file = '%s'
; END WP SECURITY FIREWALL
", addcslashes($firewallPath, "'"));

                    break;
            }

            if (!empty($autoPrependIni)) {

                // Modify .user.ini
                $userIniContent = $wp_filesystem->get_contents($userIniPath);
                if (is_string($userIniContent)) {
                    $userIniContent = str_replace('auto_prepend_file', ';auto_prepend_file', $userIniContent);
                    $regex = '/; WP SECURITY FIREWALL.*?; END WP SECURITY FIREWALL/is';
                    if (preg_match($regex, $userIniContent, $matches)) {
                        $userIniContent = preg_replace($regex, $autoPrependIni, $userIniContent);
                    } else {
                        $userIniContent .= "\n\n".$autoPrependIni;
                    }
                } else {
                    $userIniContent = $autoPrependIni;
                }

                if (!$wp_filesystem->put_contents($userIniPath, $userIniContent)) {
                    throw new Exception(sprintf('We were unable to make changes to the %1$s file.
                    It\'s possible WordPress cannot write to the %1$s file because of file permissions.
                    Please verify the permissions are correct and retry the installation.', basename($userIniPath)));
                }
            }
        }
    }

    public function performIniRemoval($serverConfig, $wp_filesystem)
    {
        $htaccessPath = $this->getHtaccessPath();
        $userIniPath = $this->getUserIniPath();
        $userIni = ini_get('user_ini.filename');

        // Modify .htaccess
        $htaccessContent = $wp_filesystem->get_contents($htaccessPath);

        if (is_string($htaccessContent)) {
            $htaccessContent = preg_replace('/# WP SECURITY FIREWALL.*?# END WP SECURITY FIREWALL/is', '', $htaccessContent);
        } else {
            $htaccessContent = '';
        }

        if (!$wp_filesystem->put_contents($htaccessPath, $htaccessContent)) {
            throw new Exception('We were unable to make changes to the .htaccess file. It\'s
            possible WordPress cannot write to the .htaccess file because of file permissions, which may have been
            set by another security plugin, or you may have set them manually. Please verify the permissions allow
            the web server to write to the file, and retry the installation.');
        }
        if ($serverConfig == 'litespeed') {
            // sleep(2);
            $wp_filesystem->touch($htaccessPath);
        }

        if ($userIni) {
            // Modify .user.ini
            $userIniContent = $wp_filesystem->get_contents($userIniPath);
            if (is_string($userIniContent)) {
                $userIniContent = preg_replace('/; WP SECURITY FIREWALL.*?; END WP SECURITY FIREWALL/is', '', $userIniContent);
                $userIniContent = str_replace('auto_prepend_file', ';auto_prepend_file', $userIniContent);
            } else {
                $userIniContent = '';
            }

            if (!$wp_filesystem->put_contents($userIniPath, $userIniContent)) {
                throw new Exception(sprintf('We were unable to make changes to the %1$s file.
                It\'s possible WordPress cannot write to the %1$s file because of file permissions.
                Please verify the permissions are correct and retry the installation.', basename($userIniPath)));
            }

            return strpos($userIniContent, 'auto_prepend_file') !== false;
        }

        return false;
    }

    public function performAutoPrependFileRemoval($wp_filesystem)
    {
        $firewallPath = ABSPATH.$this->firewallFilename;
        if (!$wp_filesystem->delete($firewallPath)) {
            throw new Exception('We were unable to remove the <code>madeit-firewall.php</code> file
in the root of the WordPress installation. It\'s possible WordPress cannot remove the <code>madeit-firewall.php</code>
file because of file permissions. Please verify the permissions are correct and retry the removal.');
        }
    }

    public function getHtaccessPath()
    {
        return get_home_path().'.htaccess';
    }

    public function getUserIniPath()
    {
        $userIni = ini_get('user_ini.filename');
        if ($userIni) {
            return get_home_path().$userIni;
        }

        return false;
    }

    public function usesUserIni($serverConfig)
    {
        $userIni = ini_get('user_ini.filename');
        if (!$userIni) {
            return false;
        }
        switch ($serverConfig) {
            case 'cgi':
            case 'apache-suphp':
            case 'nginx':
            case 'litespeed':
            case 'iis':
                return true;
        }

        return false;
    }

    public function uninstall()
    {
        global $wp_filesystem;

        $htaccessPath = $this->getHtaccessPath();
        $userIniPath = $this->getUserIniPath();

        $adminURL = admin_url('/');
        $allow_relaxed_file_ownership = true;
        $homePath = dirname($htaccessPath);

        ob_start();
        if (false === ($credentials = request_filesystem_credentials(
            $adminURL,
            '',
            false,
            $homePath,
            ['version', 'locale'],
            $allow_relaxed_file_ownership
        ))
        ) {
            ob_end_clean();

            return false;
        }

        if (!WP_Filesystem($credentials, $homePath, $allow_relaxed_file_ownership)) {
            // Failed to connect, Error and request again
            request_filesystem_credentials(
                $adminURL,
                '',
                true,
                ABSPATH,
                ['version', 'locale'],
                $allow_relaxed_file_ownership
            );
            ob_end_clean();

            return false;
        }

        if ($wp_filesystem->errors->get_error_code()) {
            ob_end_clean();

            return false;
        }
        ob_end_clean();

        if ($wp_filesystem->is_file($htaccessPath)) {
            $htaccessContent = $wp_filesystem->get_contents($htaccessPath);
            $regex = '/# WP SECURITY FIREWALL.*?# END WP SECURITY FIREWALL/is';
            if (preg_match($regex, $htaccessContent, $matches)) {
                $htaccessContent = preg_replace($regex, '', $htaccessContent);
                if (!$wp_filesystem->put_contents($htaccessPath, $htaccessContent)) {
                    return false;
                }
            }
        }

        if ($wp_filesystem->is_file($userIniPath)) {
            $userIniContent = $wp_filesystem->get_contents($userIniPath);
            $regex = '/; WP SECURITY FIREWALL.*?; END WP SECURITY FIREWALL/is';
            if (preg_match($regex, $userIniContent, $matches)) {
                $userIniContent = preg_replace($regex, '', $userIniContent);
                if (!$wp_filesystem->put_contents($userIniPath, $userIniContent)) {
                    return false;
                }
            }
        }

        $firewallPath = ABSPATH.$this->firewallFilename;
        if ($wp_filesystem->is_file($firewallPath)) {
            $wp_filesystem->delete($firewallPath);
        }

        return true;
    }
}
