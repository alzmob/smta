<?php
namespace Smta;

use Mojavi\Form\CommonForm;

class PmtaConfig extends CommonForm {
	
	const DEBUG = true;
	const CONFIG_LOCATION_MAIN = '/etc/pmta/config';
	const CONFIG_LOCATION_DOMAIN = '/etc/pmta/domain_config';
	const CONFIG_LOCATION_BACKOFF = '/etc/pmta/backoff_config';
	
	private $ssh_session;
	
	protected $admin_ip_address;
	protected $root_username;
	protected $root_password;
	
	protected $main_config;
	protected $domain_config;
	protected $backoff_config;
	
	/**
	 * Returns the admin_ip_address
	 * @return string
	 */
	function getAdminIpAddress() {
		if (is_null($this->admin_ip_address)) {
			$this->admin_ip_address = \Smta\Setting::getSetting("ADMIN_IP_ADDRESS");
		}
		return $this->admin_ip_address;
	}
	
	/**
	 * Sets the admin_ip_address
	 * @var string
	 */
	function setAdminIpAddress($arg0) {
		$this->admin_ip_address = $arg0;
		$this->addModifiedColumn("admin_ip_address");
		return $this;
	}
	
	/**
	 * Returns the root_username
	 * @return string
	 */
	function getRootUsername() {
		if (is_null($this->root_username)) {
			$this->root_username = \Smta\Setting::getSetting("ROOT_USERNAME");
		}
		return $this->root_username;
	}
	
	/**
	 * Sets the root_username
	 * @var string
	 */
	function setRootUsername($arg0) {
		$this->root_username = $arg0;
		$this->addModifiedColumn("root_username");
		return $this;
	}
	
	/**
	 * Returns the root_password
	 * @return string
	 */
	function getRootPassword() {
		if (is_null($this->root_password)) {
			$this->root_password = \Smta\Setting::getSetting("ROOT_PASSWORD");
		}
		return $this->root_password;
	}
	
	/**
	 * Sets the root_password
	 * @var string
	 */
	function setRootPassword($arg0) {
		$this->root_password = $arg0;
		$this->addModifiedColumn("root_password");
		return $this;
	}
	
	/**
	 * Loads the configs from their respective files
	 * @return boolean
	 */
	function saveConfigs() {
		try {
			$ret_val = $this->writeRemoteFile($this->getMainConfig(), self::CONFIG_LOCATION_MAIN);
			$this->runRemoteCommand('chown pmta:pmta ' . self::CONFIG_LOCATION_MAIN);
			$this->runRemoteCommand('dos2unix ' . self::CONFIG_LOCATION_MAIN);
		} catch (\Exception $e) {
			$this->getErrors()->addError('error', $e->getMessage());
		}
		
		try {
			$ret_val = $this->writeRemoteFile($this->getDomainConfig(), self::CONFIG_LOCATION_DOMAIN);
			$this->runRemoteCommand('chown pmta:pmta ' . self::CONFIG_LOCATION_DOMAIN);
			$this->runRemoteCommand('dos2unix ' . self::CONFIG_LOCATION_DOMAIN);
		} catch (\Exception $e) {
			$this->getErrors()->addError('error', $e->getMessage());
		}
		
		try {
			$ret_val = $this->writeRemoteFile($this->getBackoffConfig(), self::CONFIG_LOCATION_BACKOFF);
			$this->runRemoteCommand('chown pmta:pmta ' . self::CONFIG_LOCATION_BACKOFF);
			$this->runRemoteCommand('dos2unix ' . self::CONFIG_LOCATION_BACKOFF);
		} catch (\Exception $e) {
			$this->getErrors()->addError('error', $e->getMessage());
		}
		return true;
	}
	
	/**
	 * Returns the main_config
	 * @return string
	 */
	function getMainConfig() {
		if (is_null($this->main_config)) {
			$this->main_config = $this->runRemoteCommand('cat ' . self::CONFIG_LOCATION_MAIN);
			if (trim($this->main_config) == 'cat: ' . self::CONFIG_LOCATION_MAIN. ': No such file or directory') {
				$this->main_config = '';
			}
		}
		return $this->main_config;
	}
	
	/**
	 * Sets the main_config
	 * @var string
	 */
	function setMainConfig($arg0) {
		$this->main_config = $arg0;
		$this->addModifiedColumn("main_config");
		return $this;
	}
	
	/**
	 * Returns the domain_config
	 * @return string
	 */
	function getDomainConfig() {
		if (is_null($this->domain_config)) {
			$this->domain_config = $this->runRemoteCommand('cat ' . self::CONFIG_LOCATION_DOMAIN);
			if (trim($this->domain_config) == 'cat: ' . self::CONFIG_LOCATION_DOMAIN. ': No such file or directory') {
				$this->domain_config = '';
			}
		}
		return $this->domain_config;
	}
	
	/**
	 * Sets the domain_config
	 * @var string
	 */
	function setDomainConfig($arg0) {
		$this->domain_config = $arg0;
		$this->addModifiedColumn("domain_config");
		return $this;
	}
	
	/**
	 * Returns the backoff_config
	 * @return string
	 */
	function getBackoffConfig() {
		if (is_null($this->backoff_config)) {
			$this->backoff_config = $this->runRemoteCommand('cat ' . self::CONFIG_LOCATION_BACKOFF);
			if (trim($this->backoff_config) == 'cat: ' . self::CONFIG_LOCATION_BACKOFF. ': No such file or directory') {
				$this->backoff_config = '';
			}
		}
		return $this->backoff_config;
	}
	
	/**
	 * Sets the backoff_config
	 * @var string
	 */
	function setBackoffConfig($arg0) {
		$this->backoff_config = $arg0;
		$this->addModifiedColumn("backoff_config");
		return $this;
	}
	
	/**
	 * Returns the ssh_session
	 * @return resource
	 */
	function getSshSession() {
		if (is_null($this->ssh_session)) {
			$this->ssh_session = $this->connect();
		}
		return $this->ssh_session;
	}
	/**
	 * Sets the ssh_session
	 * @param resource
	 */
	function setSshSession($arg0) {
		$this->ssh_session = $arg0;
		return $this;
	}
	
	/**
	 * Connects to the server and creates a session
	 * @return resource
	 */
	function connect() {
		if (trim($this->getAdminIpAddress()) != '') {
			if (($con = @ssh2_connect($this->getAdminIpAddress())) === false) {
				throw new Exception('Cannot connect to remote host ' . $this->getAdminIpAddress());
			}
		}
		if (@ssh2_auth_password($con, $this->getRootUsername(), $this->getRootPassword()) !== false) {
			return $con;
		} else {
			throw new Exception('Cannot login to ' . $this->getAdminIpAddress() . ' using password (' . $this->getRootPassword() . ')');
		}
		return null;
	}
	
	/**
	 * Disconnects to the server and creates a session
	 * @return resource
	 */
	function disconnect() {
		$this->setSshSession(null);
		return true;
	}
	
	/**
	 * Looks up the hostname on the remote server
	 * @return string
	 */
	function lookupHostname() {
		$hostname = $this->runRemoteCommand('hostname');
		$this->setHostname($hostname);
		return $hostname;
	}
	
	/**
	 * Runs a command on the remote server
	 * @return string
	 */
	function runRemoteCommand($cmd) {
		if (($stream = ssh2_exec($this->getSshSession(), $cmd, 'xterm')) !== false) {
			stream_set_blocking($stream, true);
			if (self::DEBUG) { \Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $cmd); }
			$cmd_response = stream_get_contents($stream);
			@fclose($stream);
			if (self::DEBUG) { \Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $cmd_response); }
			return $cmd_response;
		} else {
			throw new \Exception('Cannot execute commands on remote server ' . $this->getHostname());
		}
	}
	
	/**
	 * Copies a source file to the destination location
	 * @return string
	 */
	function writeRemoteFile($src_contents, $dest, $permissions = 0644) {
		$temporary_name = tempnam("/tmp/", "remote");
		file_put_contents($temporary_name, $src_contents);
		if (!ssh2_scp_send($this->getSshSession(), $temporary_name, $dest, $permissions)) {
			throw new \Exception("Could not copy to " . $dest . " on remote server.  Check that scp is installed with <code>yum install openssh-clients</code>");
		}
		return $dest;
	}
	
	/**
	 * Copies a source file to the destination location
	 * @return string
	 */
	function copyFile($src, $dest) {
		if (!@ssh2_scp_send($this->getSshSession(), $src, $dest)) {
			throw new \Exception("Could not copy script " . $src . " to remote server.  Check that scp is installed with <code>yum install openssh-clients</code>");
		}
		return $dest;
	}
}