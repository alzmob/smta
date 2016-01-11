<?php
namespace Smta\Migration\rev20141128;

use Mojavi\Migration\Migration;

class Migrate extends Migration {

	/**
	 * Upgrades to this version
	 * @return boolean
	 */
	function up() {
		try {
			// Setup the first time user, if there isn't one yet
			\Mojavi\Util\StringTools::consoleWrite('   - First User Initialization', 'Checking', \Mojavi\Util\StringTools::CONSOLE_COLOR_YELLOW);
			// Check if we have a first time user yet
			$ftp = new \Smta\Ftp();
			$active_ftp = $ftp->query(array('active' => 1), false);
			if ($active_ftp === false) {
				$ftp = new \Smta\Ftp();
				$ftp->setIsMasterFtp(true);
				$ftp->setName('smtaftp');
				$ftp->setHomeFolder('/home/smtaftp');
				$ftp->setUsername('smtaftp');
				$ftp->setPassword('smtaftp');
				$ftp->setHostname('localhost');
				$ftp_insert_id = $ftp->insert();
				
				$ftp->createFtpUser();
			} else {
				$ftp_insert_id = $ftp_active->getId();
			}
			
			$user = new \Smta\User();
			$active_user = $user->query(array('active' => 1), false);
		
			if ($active_user === false) {
				// we don't have a user yet, let's create one
		
				$admin_email = '';
		
				$user = new \Smta\User();
				$user->setName('Administrator');
				$user->setEmail($admin_email);
				$user->setUsername('admin');
				$user->setPassword(md5('admin'));
				$user->setActive(true);
				$user->setFtp($ftp_insert_id);
				$user->insert();
		
				echo "\n";
				echo "	  We have setup the first user in the system using the following credentials: \n";
				echo "	   Username: admin\n";
				echo "	   Password: admin\n";
			}
			\Mojavi\Util\StringTools::consoleWrite('   - First User Initialization', 'Done', \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
		} catch (\Exception $e) {
			\Mojavi\Util\StringTools::consoleWrite('   - First User Initialization', $e->getMessage(), \Mojavi\Util\StringTools::CONSOLE_COLOR_RED, true);
		}
	}
	
	/**
	 * Downgrades to this version
	 * @return boolean
	 */
	function down() {
	
	}
}