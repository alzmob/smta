#!/usr/bin/php
<?php
/*@todo: there still might be a problem on a FRESH checkout with creating the webapp/lib/cache folders */
try {
    //die('Please use install.php for now');

    require_once(dirname(__FILE__) . '/lib/StringTools.php');

    // Setup Vars
    $init_dir = dirname(__FILE__) . '/';
    $base_dir = realpath($init_dir . '../') . '/';
    $admin_base_dir = $base_dir . 'admin/';
    $api_base_dir = $base_dir . 'api/';
    $pub_base_dir = $base_dir . 'pub/';
    $settings_file = $init_dir . 'config.ini';
    
    $is_silent = false;
    
    if (isset($argv[1]) && $argv[1] == 'silent') {
        $is_silent = true;
    }
    
    // Start Output
    if (!$is_silent) {
        echo StringTools::consoleColor('Flux Installation Script', StringTools::CONSOLE_COLOR_GREEN) . "\n";
        echo StringTools::consoleColor(str_repeat('=', 50), StringTools::CONSOLE_COLOR_GREEN) . "\n";
        
        echo 'Configuring your application';
        echo "\n";
    }
    
    if (!$is_silent) { StringTools::consoleWrite(' - loading settings', 'Reading', StringTools::CONSOLE_COLOR_YELLOW); }
    if (file_exists($settings_file)) {
        $ini_settings = parse_ini_file($settings_file, true);
    } else {
        throw new Exception('We cannot find the config.ini settings file.  Please create one with your settings in it before continuing.  You can use the config.ini.sample as a reference.');
    }
    if (!$is_silent) { StringTools::consoleWrite(' - loading settings', 'Loaded', StringTools::CONSOLE_COLOR_GREEN, true); }
    
    // +------------------------------------------------------------------------+
    // | Setup the DAO modules and any other links that need to be setup        |
    // +------------------------------------------------------------------------+
    $lib_folders = array(
        'Flux' => 'Flux',
        'Mojavi' => 'Mojavi',
        'Zend' => 'Zend',
    );
    
    
    foreach ($lib_folders as $lib_name => $lib_folder) {
        if (!$is_silent) { StringTools::consoleWrite(' - dao path', $lib_folder, StringTools::CONSOLE_COLOR_YELLOW); }
        if (file_exists($admin_base_dir . 'webapp/lib/' . $lib_folder) && is_dir($admin_base_dir . 'webapp/lib/' . $lib_folder)) {
            // Setup lib for api
            if (!file_exists($api_base_dir . "webapp/lib/" . $lib_folder) && !is_link($api_base_dir . "webapp/lib/" . $lib_folder)) {
                $cmd = 'ln -s ' . $admin_base_dir . '/webapp/lib/' . $lib_folder . ' ' . $api_base_dir . 'webapp/lib/' . $lib_folder;
                shell_exec($cmd);
            } else if (is_link($api_base_dir . "webapp/lib/" . $lib_folder)) {
                $cmd = 'rm -f  ' . $api_base_dir . 'webapp/lib/' . $lib_folder;
                shell_exec($cmd);
                
                $cmd = 'ln -s ' . $admin_base_dir . 'webapp/lib/' . $lib_folder . ' ' . $api_base_dir . 'webapp/lib/' . $lib_folder;
                shell_exec($cmd);
            }
            // Setup lib for pub
            if (!file_exists($pub_base_dir . "webapp/lib/" . $lib_folder) && !is_link($pub_base_dir . "webapp/lib/" . $lib_folder)) {
                $cmd = 'ln -s ' . $admin_base_dir . '/webapp/lib/' . $lib_folder . ' ' . $pub_base_dir . 'webapp/lib/' . $lib_folder;
                shell_exec($cmd);
            } else if (is_link($pub_base_dir . "webapp/lib/" . $lib_folder)) {
                $cmd = 'rm -f  ' . $pub_base_dir . 'webapp/lib/' . $lib_folder;
                shell_exec($cmd);
                
                $cmd = 'ln -s ' . $admin_base_dir . 'webapp/lib/' . $lib_folder . ' ' . $pub_base_dir . 'webapp/lib/' . $lib_folder;
                shell_exec($cmd);
            }
        }
    }
    if (!$is_silent) { StringTools::consoleWrite(' - dao path', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
    
    if (!$is_silent) { StringTools::consoleWrite(' - vendors', 'linking', StringTools::CONSOLE_COLOR_YELLOW); }
    if (file_exists($admin_base_dir . 'webapp/vendor/') && is_dir($admin_base_dir . 'webapp/vendor/')) {
        // Setup lib for api
        if (!file_exists($api_base_dir . 'webapp/vendor') && !is_link($api_base_dir . 'webapp/vendor')) {
            $cmd = 'ln -s ' . $admin_base_dir . '/webapp/vendor' . ' ' . $api_base_dir . 'webapp/vendor';
            shell_exec($cmd);
        } else if (file_exists($api_base_dir . 'webapp/vendor/') && is_link($api_base_dir . 'webapp/vendor/')) {
            $cmd = 'rm -f  ' . $api_base_dir . 'webapp/vendor/';
            shell_exec($cmd);
            
            $cmd = 'ln -s ' . $admin_base_dir . 'webapp/vendor' . ' ' . $api_base_dir . 'webapp/vendor';
            shell_exec($cmd);
        }
        if (!file_exists($pub_base_dir . 'webapp/vendor') && !is_link($pub_base_dir . 'webapp/vendor')) {
            $cmd = 'ln -s ' . $admin_base_dir . '/webapp/vendor' . ' ' . $pub_base_dir . 'webapp/vendor';
            shell_exec($cmd);
        } else if (file_exists($pub_base_dir . 'webapp/vendor/') && is_link($pub_base_dir . 'webapp/vendor/')) {
            $cmd = 'rm -f  ' . $pub_base_dir . 'webapp/vendor/';
            shell_exec($cmd);
            
            $cmd = 'ln -s ' . $admin_base_dir . 'webapp/vendor' . ' ' . $pub_base_dir . 'webapp/vendor';
            shell_exec($cmd);
        }
	}
    
    if (!$is_silent) { StringTools::consoleWrite(' - vendors', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
    
    // +------------------------------------------------------------------------+
    // | Select which databases.ini file to use for this project.  Any file in  |
    // | /init that starts with databases_* will be enumerated and displayed as |
    // | a list of choices to the user                                          |
    // +------------------------------------------------------------------------+
    if (file_exists($init_dir . "/config/databases.ini")) {
        if (!$is_silent) { StringTools::consoleWrite(' - databases', 'Saving', StringTools::CONSOLE_COLOR_YELLOW); }
        $config_contents = file_get_contents($init_dir . "/config/databases.ini");
        if (isset($ini_settings['db_host'])) {
            $config_contents = preg_replace("/<<db_host>>/", $ini_settings['db_host'], $config_contents);
        } else {
            $config_contents = preg_replace("/<<db_host>>/", 'localhost', $config_contents);
        }
        if (isset($ini_settings['db_user'])) {
            $config_contents = preg_replace("/<<db_user>>/", $ini_settings['db_user'], $config_contents);
        } else {
            $config_contents = preg_replace("/<<db_user>>/", 'root', $config_contents);
        }
        if (isset($ini_settings['db_pass'])) {
            $config_contents = preg_replace("/<<db_pass>>/", $ini_settings['db_pass'], $config_contents);
        } else {
            $config_contents = preg_replace("/<<db_pass>>/", '', $config_contents);
        }
        
        file_put_contents($admin_base_dir . "webapp/config/databases.ini", $config_contents);
        file_put_contents($api_base_dir . "webapp/config/databases.ini", $config_contents);
        file_put_contents($pub_base_dir . "webapp/config/databases.ini", $config_contents);
        if (isset($ini_settings['db_host']) && trim($ini_settings['db_host']) != '') {
            if (!$is_silent) { StringTools::consoleWrite(' - databases', $ini_settings['db_host'], StringTools::CONSOLE_COLOR_GREEN, true); }
        } else {
            if (!$is_silent) { StringTools::consoleWrite(' - databases', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
        }
    }
    
    // +------------------------------------------------------------------------+
    // | Pull in the config.php from the /init folder and configure it.  It     |
    // | will configure the configuration options which are                     |
    // |  1) Toggle Debug Mode (On=clear the cache folder on every request)     |
    // |  2) Define where the common directory is                               |
    // |  3) Define how many items to show on a list page                       |
    // |  4) Toggle HTML errors using the Errors module                         |
    // |  5) Toggle the path in Mojavi Exceptions                               |
    // +------------------------------------------------------------------------+
    if (file_exists($init_dir . "/config/config.php.init")) {
        if (!$is_silent) { StringTools::consoleWrite(' - mojavi framework', 'Saving', StringTools::CONSOLE_COLOR_YELLOW); }
        $config_contents = file_get_contents($init_dir . "/config/config.php.init");
        if (isset($ini_settings['common_path'])) {
            $config_contents = preg_replace("/<<common_path>>/", $ini_settings['common_path'], $config_contents);
        } else {
            $config_contents = preg_replace("/<<common_path>>/", '', $config_contents);
        }
        
        file_put_contents($admin_base_dir . "webapp/config.php", $config_contents);
        file_put_contents($api_base_dir . "webapp/config.php", $config_contents);
        file_put_contents($pub_base_dir . "webapp/config.php", $config_contents);
        if (isset($ini_settings['common_path']) && trim($ini_settings['common_path']) != '') {
            if (!$is_silent) { StringTools::consoleWrite(' - mojavi framework', $ini_settings['common_path'], StringTools::CONSOLE_COLOR_GREEN, true); }
        } else {
            if (!$is_silent) { StringTools::consoleWrite(' - mojavi framework', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
        }
    }
    
    // +------------------------------------------------------------------------+
    // | Create the .htaccess file in /docroot.  This is used to configure per  |
    // | application settings.  Currently it will:                              |
    // |  1) Do nothing                                                         |
    // +------------------------------------------------------------------------+
    /*
    if (!$is_silent) { StringTools::consoleWrite(' - htaccess', 'Saving', StringTools::CONSOLE_COLOR_YELLOW); }
    $htaccess_contents[] = 'RewriteEngine On';
    $htaccess_contents[] = 'RewriteRule ^/index/(.*)/(.*).html /index.php?module=$1&action=$2 [QSA]';
    $htaccess_contents[] = 'RewriteRule ^/(.*)/(.*).html /index.php?module=$1&action=$2 [QSA]';
    file_put_contents($admin_base_dir . "docroot/.htaccess", $htaccess_contents);
    file_put_contents($api_base_dir . "docroot/.htaccess", $htaccess_contents);
    file_put_contents($pub_base_dir . "docroot/.htaccess", $htaccess_contents);
    if (!$is_silent) { StringTools::consoleWrite(' - htaccess', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
    */
    // +------------------------------------------------------------------------+
    // | Pull in the factories.ini from the /init folder and configure it.  It  |
    // | will configure the project name used for session handling              |
    // +------------------------------------------------------------------------+
    if (file_exists($init_dir . "/config/factories.ini")) {
        if (!$is_silent) { StringTools::consoleWrite(' - project', 'Saving', StringTools::CONSOLE_COLOR_YELLOW); }
        $factory_contents = file_get_contents($init_dir . "/config/factories.ini");
        if (isset($ini_settings['session_key'])) {
            $factory_contents = str_replace("<<session_key>>", str_replace(".", "_", $ini_settings['session_key']), $factory_contents);
        } else {
            $factory_contents = str_replace("<<session_key>>", basename(dirname($init_dir)), $factory_contents);
        }
        if (isset($ini_settings['session_key'])) {
            $factory_contents = str_replace("<<proj_name>>", str_replace(".", "_", $ini_settings['session_key']), $factory_contents);
        } else {
            $factory_contents = str_replace("<<proj_name>>", basename(dirname($init_dir)), $factory_contents);
        }
        file_put_contents($admin_base_dir . "webapp/config/factories.ini", $factory_contents);
        file_put_contents($api_base_dir . "webapp/config/factories.ini", $factory_contents);
        file_put_contents($pub_base_dir . "webapp/config/factories.ini", $factory_contents);
        if (!$is_silent) { StringTools::consoleWrite(' - project', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
    }
    
    // +------------------------------------------------------------------------+
    // | Pull in the logging.ini from the /init folder and configure it.  It    |
    // | will configure the logging options which are                           |
    // |  1) whether to send out email notifications                            |
    // |  2) whether to turn on development logging                             |
    // |  3) if development logging is on, where to write the development log   |
    // +------------------------------------------------------------------------+
    if (file_exists($init_dir . "/config/logging.ini")) {
        if (!$is_silent) { StringTools::consoleWrite(' - logging', 'Saving', StringTools::CONSOLE_COLOR_YELLOW); }
        $logging_contents = file_get_contents($init_dir . "/config/logging.ini");
        file_put_contents($admin_base_dir . "webapp/config/logging.ini", $logging_contents);
        file_put_contents($api_base_dir . "webapp/config/logging.ini", $logging_contents);
        file_put_contents($pub_base_dir . "webapp/config/logging.ini", $logging_contents);
        if (!$is_silent) { StringTools::consoleWrite(' - logging', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
    }
    
    // +------------------------------------------------------------------------+
    // | Pull in the settings.ini from the /init folder and configure it.  It   |
    // | will configure the projects theme                                      |
    // +------------------------------------------------------------------------+
    if (file_exists($init_dir . "/config/settings.ini")) {
        if (!$is_silent) { StringTools::consoleWrite(' - settings', 'Saving', StringTools::CONSOLE_COLOR_YELLOW); }
        $settings_contents = file_get_contents($init_dir . "/config/settings.ini");
        if (isset($ini_settings['api_server'])) {
            $settings_contents = str_replace("<<api_url>>", $ini_settings['api_server'], $settings_contents);
        } else {
            $hostname = trim(shell_exec('hostname'));
            if (substr_count($hostname, '.') < 2) {
                $hostname = 'api.' . $hostname;
            } else {
                $hostname = substr($hostname, (strpos($hostname, '.') + 1));
                $hostname = 'api.' . $hostname;
            }
            $settings_contents = str_replace("<<api_url>>", 'http://' . $hostname, $settings_contents);
        }
        if (isset($ini_settings['theme'])) {
            $settings_contents = str_replace("<<theme_folder>>", $ini_settings['theme'], $settings_contents);
        } else {
            $settings_contents = str_replace("<<theme_folder>>", 'default', $settings_contents);
        }
        if (isset($ini_settings['items_per_page'])) {
            $settings_contents = preg_replace("/<<items_per_page>>/", $ini_settings['items_per_page'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<items_per_page>>/", '50', $settings_contents);
        }
        if (isset($ini_settings['upload_folder'])) {
            $settings_contents = preg_replace("/<<upload_folder>>/", $ini_settings['upload_folder'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<upload_folder>>/", '%MO_WEBAPP_DIR%/meta/uploads', $settings_contents);
        }
        if (isset($ini_settings['upload_username'])) {
            $settings_contents = preg_replace("/<<upload_username>>/", $ini_settings['upload_username'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<upload_username>>/", 'flux', $settings_contents);
        }
        if (isset($ini_settings['upload_password'])) {
            $settings_contents = preg_replace("/<<upload_password>>/", $ini_settings['upload_password'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<upload_password>>/", 'm3g@gun', $settings_contents);
        }
        if (isset($ini_settings['upload_host'])) {
            $settings_contents = preg_replace("/<<upload_host>>/", $ini_settings['upload_host'], $settings_contents);
        } else {
            $hostname = trim(shell_exec('hostname'));
            $settings_contents = preg_replace("/<<upload_host>>/", $hostname, $settings_contents);
        }
        if (isset($ini_settings['log_folder'])) {
            $settings_contents = preg_replace("/<<log_folder>>/", $ini_settings['log_folder'], $settings_contents);
            
            $cache_user = isset($ini_settings['cache_user']) ? $ini_settings['cache_user'] : 'apache';
            $cache_group = isset($ini_settings['cache_group']) ? $ini_settings['cache_group'] : 'apache';
            
            passthru('chown ' . $cache_user . ':' . $cache_group . ' ' . $ini_settings['log_folder']. ' -Rf');
            passthru('chmod 775 ' . $ini_settings['log_folder']. ' -Rf');
        } else {
            $settings_contents = preg_replace("/<<log_folder>>/", '/var/log/flux', $settings_contents);
            
            $cache_user = isset($ini_settings['cache_user']) ? $ini_settings['cache_user'] : 'apache';
            $cache_group = isset($ini_settings['cache_group']) ? $ini_settings['cache_group'] : 'apache';
            
            passthru('chown ' . $cache_user . ':' . $cache_group . ' /var/log/flux -Rf');
            passthru('chmod 775 /var/log/flux -Rf');
        }
        if (isset($ini_settings['realtime_url'])) {
            $settings_contents = preg_replace("/<<realtime_url>>/", $ini_settings['realtime_url'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<realtime_url>>/", 'http://www.fluxrt.local', $settings_contents);
        }
        if (isset($ini_settings['version_file'])) {
            $settings_contents = preg_replace("/<<version_file>>/", $ini_settings['version_file'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<version_file>>/", '%MO_WEBAPP_DIR%/meta/version', $settings_contents);
        }
        if (isset($ini_settings['mail_hostname']) && trim($ini_settings['mail_hostname']) != '') {
            $settings_contents = preg_replace("/<<mail_hostname>>/", $ini_settings['mail_hostname'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<mail_hostname>>/", '', $settings_contents);
        }
        if (isset($ini_settings['mail_username']) && trim($ini_settings['mail_username']) != '') {
            $settings_contents = preg_replace("/<<mail_username>>/", $ini_settings['mail_username'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<mail_username>>/", '', $settings_contents);
        }
        if (isset($ini_settings['mail_password']) && trim($ini_settings['mail_password']) != '') {
            $settings_contents = preg_replace("/<<mail_password>>/", $ini_settings['mail_password'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<mail_password>>/", '', $settings_contents);
        }
        if (isset($ini_settings['notification_email'])) {
            $settings_contents = preg_replace("/<<notification_email>>/", $ini_settings['notification_email'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<notification_email>>/", '', $settings_contents);
        }
        if (isset($ini_settings['use_apc'])) {
            $settings_contents = preg_replace("/<<use_apc>>/", $ini_settings['use_apc'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<use_apc>>/", '0', $settings_contents);
        }
        if (isset($ini_settings['cache_user'])) {
            $settings_contents = preg_replace("/<<cache_user>>/", $ini_settings['cache_user'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<cache_user>>/", 'apache', $settings_contents);
        }
        if (isset($ini_settings['cache_group'])) {
            $settings_contents = preg_replace("/<<cache_group>>/", $ini_settings['cache_group'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<cache_group>>/", 'apache', $settings_contents);
        }
        if (isset($ini_settings['password_salt'])) {
            $settings_contents = preg_replace("/<<password_salt>>/", $ini_settings['password_salt'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<password_salt>>/", 'password', $settings_contents);
        }
        if (isset($ini_settings['page2images_api'])) {
            $settings_contents = preg_replace("/<<page2images_api>>/", $ini_settings['page2images_api'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<page2images_api>>/", '108709d8d7ae991c', $settings_contents);
        }
        if (isset($ini_settings['analytic_domain'])) {
            $settings_contents = preg_replace("/<<analytic_domain>>/", $ini_settings['analytic_domain'], $settings_contents);
        } else {
            $settings_contents = preg_replace("/<<analytic_domain>>/", 'directdrivehosting.com', $settings_contents);
        }
        file_put_contents($admin_base_dir . "webapp/config/settings.ini", $settings_contents);
        file_put_contents($api_base_dir . "webapp/config/settings.ini", $settings_contents);
        file_put_contents($pub_base_dir . "webapp/config/settings.ini", $settings_contents);
        if (!$is_silent) { StringTools::consoleWrite(' - settings', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
    }

    // +------------------------------------------------------------------------+
    // | Clears the /webapp/cache folder.  This is to force the application to  |
    // | re-cache all of its configuration settings.  We do this at the very    |
    // | end so that all of our new changes take effect.                        |
    // +------------------------------------------------------------------------+
    $cache_folders = array(
        $admin_base_dir . 'webapp/cache/',
        $api_base_dir . 'webapp/cache/',
        $pub_base_dir . 'webapp/cache/'
    );
    foreach ($cache_folders as $cache_folder) {
        if ($cache_handle = opendir($cache_folder)) {
            if (!$is_silent) { StringTools::consoleWrite(' - clearing cache', 'Clearing', StringTools::CONSOLE_COLOR_YELLOW); }
            while(($file = readdir($cache_handle)) !== false) {
                if (strpos($file, '.') !== 0) {
                    unlink($cache_folder . "/" . $file);
                }
            }
            closedir($cache_handle);
        }
    }
        
    if (function_exists('apc_clear_cache')) {
        apc_clear_cache();
        apc_clear_cache('user');
    }
    if (!$is_silent) { StringTools::consoleWrite(' - clearing cache', 'Cleared', StringTools::CONSOLE_COLOR_GREEN, true); }
    
    // +------------------------------------------------------------------------+
    // | Make sure that certain folders in the project have the correct         |
    // | permissions.                                                           |
    // +------------------------------------------------------------------------+
    if (!$is_silent) { StringTools::consoleWrite(' - permissions', 'Setting', StringTools::CONSOLE_COLOR_YELLOW); }
    $global_folders = array(
        "Cache (admin)" => $admin_base_dir . 'webapp/cache/',
        "Meta (admin)" => $admin_base_dir . 'webapp/meta/',
        "Cache (api)" => $api_base_dir . 'webapp/cache/',
        "Meta (api)" => $api_base_dir . 'webapp/meta/',
        "Cache (pub)" => $pub_base_dir . 'webapp/cache/',
        "Meta (pub)" => $pub_base_dir . 'webapp/meta/',
    );
    
    foreach ($global_folders as $key => $global_folder) {
        if (file_exists($global_folder)) {
            `chmod 0777 $global_folder -Rf`;
        } else {
            mkdir($global_folder);
            `chmod 0777 $global_folder -Rf`;
        }
    }
    if (!$is_silent) { StringTools::consoleWrite(' - permissions', 'Saved', StringTools::CONSOLE_COLOR_GREEN, true); }
} catch (Exception $e) {
    echo StringTools::consoleColor($e->getMessage(), StringTools::CONSOLE_COLOR_RED) . "\n";
}
?>