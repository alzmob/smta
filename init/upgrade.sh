#!/usr/bin/php
<?php
use Mojavi\Util\StringTools;

try {
    set_time_limit(0);
    require_once(dirname(__FILE__) . '/lib/Connection.php');
    require_once(dirname(__FILE__) . '/../admin/webapp/config.php');
    require_once(dirname(__FILE__) . '/../admin/webapp/lib/Mojavi/mojavi.php');
    
    \Mojavi\Controller\Controller::newInstance('\Mojavi\Controller\BasicConsoleController');
    \Mojavi\Controller\Controller::getInstance()->loadContext();
    
    // Setup Vars
    $init_dir = dirname(__FILE__) . "/";
    $base_dir = $init_dir . "../admin/";
    $docroot_dir = $base_dir . "docroot/";
    $webapp_dir = $base_dir . "webapp/";
    $meta_dir = $webapp_dir . "meta/";
    $cache_dir = $webapp_dir . "cache/";
    $config_dir = $webapp_dir . "config/";
    
    $force_upgrade = false;
    $module_name = false;
    $show_help = false;
    $is_silent = false;
    $is_verbose = false;
    $migrate_greater_versions = false;
    $arg_version = '';
    
    while (count($argv) > 0) {
        $arg = array_shift($argv);
        if ($arg == 'silent' || $arg == '-s' || $arg == '--silent') {
            $is_silent = true;
        } else if ($arg == 'help' || $arg == '--help' || $arg == '-h') {
            showHelp();
            exit;
        } else if ($arg == 'verbose' || $arg == '-v' || $arg == '--verbose') {
            $is_verbose = true;
        } else if ($arg == '-f' || $arg == '--force') {
            $force_upgrade = true;
        } else if ($arg == '-g') {
            $migrate_greater_versions = true;
        } else if ($arg == '-m' || $arg == '--module' || strpos($arg, '--module') === 0) {
            if (strpos($arg, '=') !== false) {
                $module_name = substr($arg, strpos($arg, '=') + 1);
                $module_name = str_replace("\"", "", $module_name);
                $module_name = str_replace("'", "", $module_name);
            } else {
                $module_name = array_shift($argv);
            }
        } else if (trim($arg) != '/home/flux/init/upgrade.sh') {
            $arg_version = trim($arg);
        }
    }
    
    $debug = false;
    
    if ($debug) {
        echo "Silent:  " . ($is_silent ? "Yes" : "No") . "\n";
        echo "Verbose: " . ($is_verbose ? "Yes" : "No") . "\n";
        echo "Force:   " . ($force_upgrade ? "Yes" : "No") . "\n";
        echo "Greater: " . ($migrate_greater_versions ? "Yes" : "No") . "\n";
        echo "Module:  " . ($module_name ? $module_name : "All") . "\n";
        echo "Version: " . ($arg_version != '' ? $arg_version : "All") . "\n";
    }
    
    Connection::getInstance()->loadDatabasesFromFile($config_dir . '/databases.ini');
    
    // Start Output
    if (!$is_silent) {
        echo StringTools::consoleColor('Flux Upgrade Script', StringTools::CONSOLE_COLOR_GREEN) . "\n";
        echo StringTools::consoleColor(str_repeat('=', 50), StringTools::CONSOLE_COLOR_GREEN) . "\n";
    }
    
    // Check the current version number
    if (!$is_silent) { StringTools::consoleWrite('Checking current version', null, StringTools::CONSOLE_COLOR_YELLOW, true); }
    $current_version = '0';
    try {
        $preferences = new \Flux\Preferences();
        $ret_val = $preferences->query(array('key' => 'migration_version'), false);
        
        // If we don't have a version yet, the save a default one
        if (is_null($ret_val)) {
            $preferences->setKey('migration_version');
            $preferences->setValue('20000101');
            $preferences->setControl(\Flux\Preferences::READ_WRITE_ADMIN);
            $preferences->insert();
        }
        
        $current_version = $preferences->getValue();
        
        if (!$is_silent) { StringTools::consoleWrite(' - You are on version ' . $current_version, null, StringTools::CONSOLE_COLOR_GREEN, true); }
    } catch (Exception $e) {
        if (!$is_silent) { StringTools::consoleWrite('Checking current version', $e->getMessage(), StringTools::CONSOLE_COLOR_RED, true); }
    }
    
    // Now figure out what the most recent version is and start the upgrade process
    if (!$is_silent) { StringTools::consoleWrite('Scanning Revision Tree', null, StringTools::CONSOLE_COLOR_YELLOW, true); }
    $new_version = $current_version;
    $revision_array = array();
    $revision_count = 0;
    $version_folders = scandir($webapp_dir . '/lib/Flux/Migrations/');
    foreach ($version_folders as $version_folder) {
        if (strpos($version_folder, '.') === 0) { continue; }
        
        if (file_exists($webapp_dir . '/lib/Flux/Migrations/' . $version_folder)) {
            if (!$is_silent && $debug) { StringTools::consoleWrite(' - Scanning /lib/Flux/Migrations/' . $version_folder, null, StringTools::CONSOLE_COLOR_YELLOW, true); }
            $migration_revision = str_replace('rev', '', $version_folder);
            if (($migration_revision > $current_version) || ($migration_revision == $arg_version) || ($migrate_greater_versions && ($migration_revision > $arg_version))) {
                $revision_files = scandir($webapp_dir . '/lib/Flux/Migrations/' . $version_folder);
                foreach ($revision_files as $revision_file) {
                    if (strpos($revision_file, '.') === 0) { continue; }
                    if (strpos($revision_file, '.php') === false) { continue; }
                    if (!$is_silent && $debug) { StringTools::consoleWrite('   - Adding /lib/Flux/Migrations/' . $version_folder . '/' . $revision_file, null, StringTools::CONSOLE_COLOR_YELLOW, true); }
                    $revision_array[str_replace('rev', '', $migration_revision)][] = array(
                            'file' => $webapp_dir . '/lib/Flux/Migrations/' . $version_folder . '/' . $revision_file,
                            'revision' => str_replace('.', '', $version_folder));
                    $revision_count++;
                }
            }
        }
    }
    
    ksort($revision_array);
        
    if (count($revision_array) == 0) {
        if (!$is_silent) {
            echo "\n" . StringTools::consoleColor('You are already at the most recent version.', StringTools::CONSOLE_COLOR_GREEN) . "\n";
        }
    } else {
        foreach ($revision_array as $migration_version => $revision_files) {
            foreach ($revision_files as $revision_array) {
                $migration_revision = $revision_array['revision'];
                $revision_file = $revision_array['file'];
    
                if (!$is_silent) { StringTools::consoleWrite(' - Migrating to ' . $migration_revision, 'Updating ' . count($revision_files) . ' revisions', StringTools::CONSOLE_COLOR_GREEN, true); }
                // Include the migration file, load the class and run the up() method to upgrade
                require_once($revision_file);
                $class_name = str_replace('.php', '', basename($revision_file));
                $class_name = 'Flux\\Migrations\\' . $migration_revision . '\\' . $class_name;
                $class = new $class_name();
                if (method_exists($class, 'up')) {
                    try {
                        $class->up();
                        if (count($class->getExceptions()) > 0) {
                            if (!$is_silent) { StringTools::consoleWrite(' - Migrating to ' . $migration_revision, count($class->getExceptions()) . ' Problems', StringTools::CONSOLE_COLOR_RED, true); }
                            throw new Exception($class->getExceptionsAsString());
                        }
                        if (count($class->getWarnings()) > 0) {
                            if (!$is_silent) { StringTools::consoleWrite(' - Migrating to ' . $migration_revision, count($class->getWarnings()) . ' Warnings', StringTools::CONSOLE_COLOR_YELLOW, true); }
                            if ($is_verbose) {
                                throw new Exception($class->getWarningsAsString());
                            }
                        }
                    } catch (Exception $e) {
                        if (!$is_silent) { echo StringTools::consoleColor($e->getMessage(), StringTools::CONSOLE_COLOR_RED) . "\n"; }
                    }
                }
            }
            if (floatval($migration_version) >= floatval($new_version)) {
                $new_version = $migration_version;
            }
        }
    
        // Finally update our current version
        if (!$is_silent) { StringTools::consoleWrite('Updating version', 'Writing', StringTools::CONSOLE_COLOR_YELLOW); }
        try {
        	$preferences = new \Flux\Preferences();
            $preferences->updateMultiple(array('key' => 'migration_version'), array('$set' => array('value' => $new_version)));
            if (!$is_silent) { StringTools::consoleWrite('Updating version', 'Saved as ' . $new_version, StringTools::CONSOLE_COLOR_GREEN, true); }
        } catch (Exception $e) {
            if (!$is_silent) { StringTools::consoleWrite('Updating version', $e->getMessage(), StringTools::CONSOLE_COLOR_RED, true); }
        }
    }
} catch (Exception $e) {
    echo StringTools::consoleColor($e->getMessage(), StringTools::CONSOLE_COLOR_RED) . "\n";
}

/**
 * Shows the help message and exits
 */
function showHelp() {
    $buffer = array();
    $buffer[] = "Upgrade Script v1.0";
    $buffer[] = str_repeat('=', 50);
    $buffer[] = "";
    $buffer[] = " -h, --help            Shows this help message";
    $buffer[] = " -v, --verbose         Turn on verbose output";
    $buffer[] = " -s, --silent          Turn off output, run silent";
    $buffer[] = " -f, --force           Force an upgrade, even if you are on a newer version";
    $buffer[] = " -m, --module          Only perform the upgrade on the specific module";
    $buffer[] = " -g                    Also migrate to versions greater than the one specified";
    $buffer[] = " VERSION               Upgrade to the specific version ( format: 20130608 )";
    $buffer[] = "";
    $buffer[] = "Usage: upgrade.sh [svh] [VERSION]";
    $buffer[] = "";
    echo implode("\n", $buffer);
}

?>
