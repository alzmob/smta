<?php
namespace Smta;

class Ftp extends Base\Ftp {
	
	private $html_input_element_id;
	private $files;
	protected $use_active_mode;
	protected $filename;
	protected $content;
	protected $ftp_log;
	
	private $change_credentials;
	private $_connection_id;

	/**
	 * Returns the use_active_mode
	 * @return boolean
	 */
	function getUseActiveMode() {
		if (is_null($this->use_active_mode)) {
			$this->use_active_mode = false;
		}
		return $this->use_active_mode;
	}
	
	/**
	 * Sets the use_active_mode
	 * @var boolean
	 */
	function setUseActiveMode($arg0) {
		$this->use_active_mode = (boolean)$arg0;
		$this->addModifiedColumn("use_active_mode");
		return $this;
	}
	
	/**
	 * Returns the files
	 * @return string
	 */
	function getFiles() {
		if (is_null($this->files)) {
			$this->files = $this->downloadFiles();
		}
		return $this->files;
	}
	
	/**
	 * Returns the folder name
	 * @return string
	 */
	function setFolderName($arg0) {
		if (strpos($arg0, '/') === 0) {
			return parent::setFolderName(substr($arg0, 1));
		} else if (strpos($arg0, './') === 0) {
			return parent::setFolderName(substr($arg0, 2));
		} else if (strpos($arg0, '../') === 0) {
			return parent::setFolderName(substr($arg0, 3));
		} else if (strpos($arg0, '.') === 0) {
			return parent::setFolderName(substr($arg0, 1));
		}
		return parent::setFolderName($arg0);
	}
	
	/**
	 * Returns the ftp_log
	 * @return string
	 */
	function getFtpLog() {
		if (is_null($this->ftp_log)) {
			$this->ftp_log = "";
		}
		return $this->ftp_log;
	}
	
	/**
	 * Sets the ftp_log
	 * @var string
	 */
	function setFtpLog($arg0) {
		$this->ftp_log = $arg0;
		$this->addModifiedColumn('ftp_log');
		return $this;
	}
	
	/**
	 * Returns the change_credentials
	 * @return boolean
	 */
	function getChangeCredentials() {
		if (is_null($this->change_credentials)) {
			$this->change_credentials = false;
		}
		return $this->change_credentials;
	}
	
	/**
	 * Sets the change_credentials
	 * @var boolean
	 */
	function setChangeCredentials($arg0) {
		$this->change_credentials = (boolean)$arg0;
		$this->addModifiedColumn('change_credentials');
		return $this;
	}

	/**
	 * Returns the content
	 * @return string
	 */
	function getContent() {
		if (is_null($this->content)) {
			$this->content = array();
		}
		return $this->content;
	}

	/**
	 * Sets the content
	 * @var string
	 */
	function setContent($arg0) {
		$this->content = $arg0;
		return $this;
	}

	/**
	 * Returns whether we can connect or not
	 * @return string
	 */
	function getFileName() {
		if (is_null($this->filename)) {
			$this->filename = '';
		}
		return $this->filename;
	}

	/**
	 * Sets the filename
	 * @var string
	 */
	function setFileName($arg0) {
		$this->filename = $arg0;
		return $this;
	}
	
	/**
	 * Returns the html_input_element_id
	 * @return string
	 */
	function getHtmlInputElementId() {
		if (is_null($this->html_input_element_id)) {
			$this->html_input_element_id = "";
		}
		return $this->html_input_element_id;
	}
	
	/**
	 * Sets the html_input_element_id
	 * @var string
	 */
	function setHtmlInputElementId($arg0) {
		$this->html_input_element_id = $arg0;
		$this->addModifiedColumn('html_input_element_id');
		return $this;
	}
	
	/**
	 * Generates a new FTP username
	 * @return string
	 */
	function generateUsername($name) {
		$username = strtolower($name);
		$username = preg_replace("/[^a-z0-9]/", "", $username);
		$counter = 1;
		while ($this->isFtpUserExists($username)) {
			$username .= $counter;
			$counter++;
		}
		$this->setUsername($username);
		return $username;
	}
	
	/**
	 * Generates a new FTP password
	 * @return string
	 */
	function generatePassword($name) {
		$password = substr(md5($name . strtotime('now')), 0, 8);
		$this->setPassword($password);
		return $password;
	}
	
	/**
	 * Checks if the user already exists on this server
	 * @return boolean
	 */
	function isFtpUserExists($username = null) {
		if (is_null($username)) {
			$username = $this->getUsername();
		}
		// Check if this username exists in the /etc/passwd file
		$cmd = 'grep "^' . escapeshellcmd($username) . ':" /etc/passwd | wc -l';
		$user_exists = intval(trim(shell_exec($cmd)));
		return ($user_exists > 0);
	}
	
	/**
	 * Adds a new FTP user to the system
	 * @return boolean
	 */
	function createFtpUser() {
		if ($this->isFtpUserExists()) {
			$encrypted_password = crypt($this->getPassword(), 'abcd1234');
			$cmd = 'usermod -s /sbin/nologin -p ' . escapeshellcmd($encrypted_password) . ' ';
			if ($this->getHomeFolder() != '') {
				$cmd .= ' -d ' . $this->getHomeFolder();
			}
			$cmd .= (' ' . escapeshellcmd($this->getUsername()));
			$ret_val = trim(shell_exec($cmd));
			if ($ret_val == '') {
				// If there are no errors, then set the password
			} else {
				throw new \Exception($ret_val);
			}
		} else {
			// add the new user with /sbin/nologin so they have FTP access, but no ssh access
			$encrypted_password = crypt($this->getPassword(), 'abcd1234');
			$cmd = '/usr/sbin/adduser -s /sbin/nologin -g apache --create-home -p ' . escapeshellcmd($encrypted_password);
			if ($this->getHomeFolder() != '') {
				$cmd .= ' -d ' . $this->getHomeFolder();
			}
			$cmd .= ' ' . escapeshellcmd($this->getUsername());
			$ret_val = trim(shell_exec($cmd));
			
			if ($ret_val == '') {
				if ($this->getHomeFolder() != '') {
					$cmd = 'chmod 0775 ' . $this->getHomeFolder();
					shell_exec($cmd);
				}
				// If there are no errors, then set the password
			} else {
				throw new \Exception($ret_val);
			}
		}
		
		$dirs_to_create = array();
		if ($this->getHomeFolder() != '') {
			$dirs_to_create[] = $this->getHomeFolder() . '/processed';
			$dirs_to_create[] = $this->getHomeFolder() . '/exports';
			$dirs_to_create[] = $this->getHomeFolder() . '/imports';
			$dirs_to_create[] = $this->getHomeFolder() . '/bounces';
			$dirs_to_create[] = $this->getHomeFolder() . '/delivered';
			$dirs_to_create[] = $this->getHomeFolder() . '/unsubs';
			$dirs_to_create[] = $this->getHomeFolder() . '/raw';
		} else {
			$dirs_to_create[] = '/home/' . $this->getUsername() . '/processed';
			$dirs_to_create[] = '/home/' . $this->getUsername() . '/exports';
			$dirs_to_create[] = '/home/' . $this->getUsername() . '/imports';
			$dirs_to_create[] = '/home/' . $this->getUsername() . '/bounces';
			$dirs_to_create[] = '/home/' . $this->getUsername() . '/unsubs';
			$dirs_to_create[] = '/home/' . $this->getUsername() . '/delivered';
			$dirs_to_create[] = '/home/' . $this->getUsername() . '/raw';
		}
		foreach ($dirs_to_create as $dir_to_create) {
			if (!file_exists($dir_to_create)) {
				$cmd = 'mkdir -m 0775 -p ' . $dir_to_create;
				shell_exec($cmd);
				$cmd = 'chown ' . $this->getUsername() . ':apache ' . $dir_to_create;
				shell_exec($cmd);
			}
		}
		
		return true;
	}
	
	/**
	 * Changes the password on an FTP user
	 * @return boolean
	 */
	function changePassword() {
		if (!$this->isFtpUserExists()) {
			// add the new user with /sbin/nologin so they have FTP access, but no ssh access
			$encrypted_password = crypt($this->getPassword(), 'abcd1234');
			$cmd = '/usr/sbin/adduser -s /sbin/nologin -g apache --create-home -p ' . escapeshellcmd($encrypted_password);
			if ($this->getHomeFolder() != '') {
				$cmd .= ' -d ' . $this->getHomeFolder();
			}
			$cmd .= ' ' . escapeshellcmd($this->getUsername());
			$ret_val = trim(shell_exec($cmd));
			if ($ret_val == '') {
				if ($this->getHomeFolder() != '') {
					$cmd = 'chmod 0775 ' . $this->getHomeFolder();
					shell_exec($cmd);
				}
				// If there are no errors, then set the password
			} else {
				throw new \Exception($ret_val);
			}
		} else {
			// change the password on an existing user with /sbin/nologin so they have FTP access, but no ssh access
			$encrypted_password = crypt($this->getPassword(), 'abcd1234');
			$cmd = 'usermod -p ' . escapeshellcmd($encrypted_password); 
			if ($this->getHomeFolder() != '') {
				$cmd .= (' -d ' . $this->getHomeFolder());
			}
			$cmd .= (' ' . escapeshellcmd($this->getUsername()));
			$ret_val = trim(shell_exec($cmd));
			if ($ret_val == '') {
				// If there are no errors, then set the password
			} else {
				throw new \Exception($ret_val);
			}
		}
		
		return true;
	}
	
	/**
	 * Disables a current user on the system
	 * @return boolean
	 */
	function disableFtpUser() {
		// disable an exising user with /sbin/false so they have no FTP access and no ssh access
		if (trim($this->getUsername()) != '') {
			$cmd = 'usermod -s /bin/false ' . escapeshellcmd($this->getUsername());
			$ret_val = trim(shell_exec($cmd));
			if ($ret_val == '') {
				// If there are no errors, then set the password
			} else {
				throw new \Exception($ret_val);
			}
		} else {
			parent::delete();
		}
		
		return true;
	}
	
	/**
	 * Tests the FTP connection
	 * @return boolean
	 */
	function testFtp() {
		try {
			// Test the connection
			$this->getFtpConnection();
			return true;
		} catch (\Exception $e) {
			\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $e->getMessage());
			throw $e;
		}
		return false;
	}
	
	/**
	 * Inserts a new ftp user only if the user does not already exist
	 * @return boolean
	 */
	function update($criteria_array = array(), $update_array = array(), $options_array = array('upsert' => true), $use_set_notation = false) {
		// If we need to alter the password, then do it here
		if ($this->getChangeCredentials()) {
			$this->setStatus(self::STATUS_PENDING_PASSWORD);
		}
		return parent::update();
	}
	
	/**
	 * Inserts a new ftp user only if the user does not already exist
	 * @return boolean
	 */
	function insert() {
		if ($this->isFtpUserExists()) {
			throw new \Exception('The username ' . $this->getUsername() . ' already exists as an FTP account and cannot be added');
		}
		return parent::insert();
	}
	
	/**
	 * Deletes this FTP account by actually setting the status to inactive
	 * @return boolean
	 */
	function delete() {
		if (\MongoId::isValid($this->getId()) && trim($this->getUsername()) != '') {
			$this->setStatus(self::STATUS_PENDING_INACTIVE);
			return $this->update();
		} else if (\MongoId::isValid($this->getId())) {
			return parent::delete();
		}
		return false;
	}
	
	/**
	 * Returns the FTP Connection id, or connects if not already connected
	 * @return resource|null
	 */
	function getFtpConnection() {
		if (is_null($this->_connection_id)) {
			$this->_connection_id = @ftp_connect($this->getHostname(), $this->getPort(), 5);
			if ($this->_connection_id !== false) {
				if (is_null($this->_connection_id)) {
					throw new \Exception('Cannot connect to ftp "' . $this->getHostname() . '" because the _connection_id is null');
				}
				if (!@ftp_login($this->_connection_id, $this->getUsername(), $this->getPassword())) {
					throw new \Exception('We are able to connect to "' . $this->getHostname() . '", but cannot login using the username "' . $this->getUsername() . '" and password');
				}
			} else {
				throw new \Exception('Cannot connect to "' . $this->getHostname() . '", check the hostname and that <code>vsftpd</code> is running');
			}
		}
		return $this->_connection_id;
	}

	/**
	 * Downloads a list of files from the ftp server
	 * Format of returned array is
	 *  - [type] = directory|file
	 *  - [rights] = drwxrwx---
	 *  - [number] = integer
	 *  - [user] = owner name
	 *  - [group] = group name
	 *  - [size] = size in bytes
	 *  - [month] = last modified month
	 *  - [day] = last modified day
	 *  - [time] = last modified time
	 * @return array
	 */
	private function downloadFiles() {
		try {
			$connection = $this->getFtpConnection();
			
			// Strip the first slash from folder names
			if (strpos($this->getFolderName(), '/') === 0) {
				$folder_name = substr($this->getFolderName(), 1);
			} else {
				$folder_name = $this->getFolderName();
			}
			
			// Enable passive mode
			if ($this->getUseActiveMode()) {
				ftp_pasv($connection, false);
			} else {
				ftp_pasv($connection, true);
			}
			
			// Pull down the list of files
			$raw_list = ftp_rawlist($connection, $folder_name);
			
			if (is_array($raw_list)) {
				$file_list = array(); 
				foreach ($raw_list as $list_item) { 
					$chunks = preg_split("/\s+/", $list_item); 
					list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks; 
					$item['type'] = $chunks[0]{0} === 'd' ? 'directory' : 'file'; 
					array_splice($chunks, 0, 8); 
					$filename = implode(" ", $chunks);
					// Apply a file filter if we have one
					if (trim($this->getFileFilter()) == '') {
						$file_list[$filename] = $item;
					} else {
						if (preg_match($this->getFileFilter(), $filename) != false) {
							$file_list[$filename] = $item;
						}
					} 
				} 
				return $file_list;
			}
		} catch (\Exception $e) {
			throw $e;
		}
		return array();
	}

	/**
	 * Downloads a sample of a file (the first 10 lines)
	 * If the file is compressed, it is uncompressed automatically for gz and zip files
	 * @return string
	 */
	function getRemoteFileSample() {
		$tempnam = tempnam('/tmp', 'ftp');
		chgrp($tempnam, "apache");
		chmod($tempnam, 0775);
		$file_xfer_flag = FTP_ASCII;
		if (substr(strtolower($this->getFilename()), -3) == '.gz') {
			$file_xfer_flag = FTP_BINARY;
		}
		
		if (($connection_id = $this->getFtpConnection()) !== false) {
			if ($this->getUseActiveMode()) {
				ftp_pasv($connection_id, false);
			} else {
				ftp_pasv($connection_id, true);
			}
			// Disable Autoseek
			ftp_set_option($connection_id, FTP_AUTOSEEK, false);
			$ret = ftp_nb_get($connection_id, $tempnam, $this->getFilename(), $file_xfer_flag);
			while ($ret == FTP_MOREDATA) {
				if (count(file($tempnam)) > 20) {
					$ret = FTP_FINISHED;
					break;
				}
				
   				// Continue downloading...
   				$ret = ftp_nb_continue($connection_id);
			}
			if ($ret != FTP_FINISHED) {
				throw new \Exception('There was an error downloading the file ' . $this->getFilename() . ' from ' . $this->getUsername() . '@' . $this->getHostname());
			}
		}
			
		if (substr($this->getFilename(), -3) == '.gz') {
			$file_contents = gzfile($tempnam);
			return implode("", $file_contents);	
		} else {
			return file_get_contents($tempnam);
		}
	}
	
	/**
	 * Moves a remote file into a subdirectory
	 * @return string
	 */
	function moveRemoteFile() {
		if (($connection_id = $this->getFtpConnection()) !== false) {
			if ($this->getUseActiveMode()) {
				ftp_pasv($connection_id, false);
			} else {
				ftp_pasv($connection_id, true);
			}
			$old_file_name = basename($this->getFilename());
			$old_dir_name = dirname($this->getFilename());
			if ($old_dir_name != '') {
				ftp_chdir($connection_id, $old_dir_name);
			}
			$new_filename = "processed/" . $old_file_name;
			// Verify that the processed folder exists
			if (ftp_nlist($connection_id, "processed/") === false) {
				ftp_mkdir($connection_id, "processed/");
			}
			return ftp_rename($connection_id, $old_file_name, $new_filename);
		}
		return false;
	}
	
	/**
	 * Downloads a full file
	 * @return string
	 */
	function deleteRemoteFile() {
		if (($connection_id = $this->getFtpConnection()) !== false) {
			if ($this->getUseActiveMode()) {
				ftp_pasv($connection_id, false);
			} else {
				ftp_pasv($connection_id, true);
			}
			$dirname = dirname($this->getFilename());
			$filename = basename($this->getFilename());
			if ($dirname != '') {
				ftp_chdir($connection_id, $dirname);
			}
			return ftp_delete($connection_id, $filename);
		}
		return false;
	}
	
	/**
	 * Downloads a full file
	 * @return string
	 */
	function getRemoteFileSize() {
		if (($connection_id = $this->getFtpConnection()) !== false) {
			if ($this->getUseActiveMode()) {
				ftp_pasv($connection_id, false);
			} else {
				ftp_pasv($connection_id, true);
			}
			return ftp_size($connection_id, $this->getFilename());
		}
		return 0;
	}
	
	/**
	 * Downloads a full file
	 * @return string
	 */
	function getRemoteFile($callback = null) {
		// Initiate
		$tempnam = tempnam('/tmp', 'ftp');
		chgrp($tempnam, "apache");
		chmod($tempnam, 0775);
		$file_xfer_flag = FTP_ASCII;
		if (substr(strtolower($this->getFilename()), -3) == '.gz') {
			$file_xfer_flag = FTP_BINARY;
		} else if (substr(strtolower($this->getFilename()), -4) == '.zip') {
			$file_xfer_flag = FTP_BINARY;
		}
		
		if (($connection_id = $this->getFtpConnection()) !== false) {
			if ($this->getUseActiveMode()) {
				ftp_pasv($connection_id, false);
			} else {
				ftp_pasv($connection_id, true);
			}
			// Disable Autoseek
			ftp_set_option($connection_id, FTP_AUTOSEEK, false);
			$ret = ftp_nb_get($connection_id, $tempnam, $this->getFilename(), $file_xfer_flag);
			$start_time = time();
			while ($ret == FTP_MOREDATA) {
				// Continue downloading...
				if (time() - $start_time > 2) {
					if (!is_null($callback)) {
						$callback($tempnam);
					}
					$start_time = time();
				}
   				$ret = ftp_nb_continue($connection_id);
			}
			if ($ret != FTP_FINISHED) {
				throw new \Exception('There was an error downloading the file ' . $this->getFilename() . ' from ' . $this->getUsername() . '@' . $this->getHostname());
			}
		}
		return $tempnam;
	}
	
	/**
	 * Downloads a full file
	 * @return string
	 */
	function uploadLocalFile($local_file, $remote_file, $callback = null) {
		// Initiate
		if (($fh = @fopen($local_file, 'r')) !== false) {
			if (($connection_id = $this->getFtpConnection()) !== false) {
				if ($this->getUseActiveMode()) {
					ftp_pasv($connection_id, false);
				} else {
					ftp_pasv($connection_id, true);
				}
				
				// We have folders, so run chdir (or mkdir)
				if (strpos($remote_file, '/') !== false) {
					$dirs = explode("/", dirname($remote_file));
					foreach ($dirs as $dir) {
						$current_dir = ftp_pwd($connection_id);
						if (!@ftp_chdir($connection_id, $dir)) {
							// If the folder doesn't exist, create it, then move into it
							if (ftp_mkdir($connection_id, $dir)) {
								@ftp_chdir($connection_id, $dir);
							}
						}
					}
				}
				
				$remote_filename = basename($remote_file);
				
				$ret = ftp_nb_fput($connection_id, $remote_filename, $fh, FTP_ASCII);
				while ($ret == FTP_MOREDATA) {
					$file_pos = ftell($fh);
					// Continue downloading...
					if (!is_null($callback)) {
						$callback($file_pos);
					}
	   				$ret = ftp_nb_continue($connection_id);
				}
				if ($ret != FTP_FINISHED) {
					throw new \Exception('There was an error uploading the file to ' . $this->getUsername() . "@" . $this->getHostname() . ' (' . $ret . ')');
				}
			}
			fclose($fh);
		} else {
			throw new \Exception('Cannot open local file for uploading (' . $local_file . ')');
		}
		return true;
	}
}
