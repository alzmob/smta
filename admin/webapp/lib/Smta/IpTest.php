<?php
namespace Smta;

use Mojavi\Form\CommonForm;
use Mojavi\Util\StringTools;

/**
 * IpTest is used to test IPs
 * @author Mark Hobson
 */
class IpTest extends CommonForm {
	
	protected $from_domain;
	protected $seed_account;
	protected $ip_range_array;
	private $mx_server;
	private $ethernet_interface;	
	private $ip_address_array;
	private $hostname;
	
	protected $randomize_ips;
	protected $use_pipelining;
	protected $disconnect_early;
	protected $verbose;
	
	private $top_tier_domain;
	private $test_body;
	
	private $ip_result_array;
	
	protected $log_contents;
	
	protected $is_running;
	
	/**
	 * Returns the log file used when testing IPs
	 * @return string
	 */
	function getLogFile() {
		return MO_LOG_FOLDER . "/ip_test_log";
	}
	
	/**
	 * Returns the log file used when testing IPs
	 * @return string
	 */
	function getLogContents() {
		if (is_null($this->log_contents)) {
			if (file_exists($this->getLogFile())) {
				$tmp_log_contents = file_get_contents($this->getLogFile());
				$tmp_log_contents = str_replace("<", "&lt;", $tmp_log_contents);
				$this->log_contents = nl2br(StringTools::consoleToHtmlColor($tmp_log_contents));
				
				if (intval(trim(shell_exec('ps aux | grep "IpTestStart" | grep -v "grep" | wc -l'))) > 0) {
					$this->setIsRunning(true);
				}
			}
		}
		return $this->log_contents;
	}
	
	/**
	 * Returns the is_running
	 * @return boolean
	 */
	function getIsRunning() {
		if (is_null($this->is_running)) {
			$this->is_running = false;
		}
		return $this->is_running;
	}
	
	/**
	 * Sets the is_running
	 * @var boolean
	 */
	function setIsRunning($arg0) {
		$this->is_running = $arg0;
		return $this;
	}
	
	/**
	 * Returns the ip_range_array
	 * @return array
	 */
	function getIpRangeArray() {
		if (is_null($this->ip_range_array)) {
			$this->ip_range_array = array();
		}
		return $this->ip_range_array;
	}
	
	/**
	 * Sets the ip_range_array
	 * @var array
	 */
	function setIpRangeArray($arg0) {
		if (is_string($arg0)) {
			if (strpos($arg0, ',') !== false) {
				$ip_lines = explode(",", $arg0);
				$this->ip_range_array = $ip_lines;
			} else {
				$ip_lines = explode("\n", $arg0);
				$this->ip_range_array = $ip_lines;
			}
		} else if (is_array($arg0)) {
			$this->ip_range_array = $arg0;
		}
		return $this;
	}
	
	/**
	 * Returns an array of all the IPs to test
	 * @return array
	 */
	function getIpAddressArray() {
		if (is_null($this->ip_address_array)) {
			$this->ip_address_array = array();
			foreach ($this->getIpRangeArray() as $ip_range) {
				if (trim($ip_range) == '') { continue; }
				$ip_range_obj = \Smta\IpRange::parseIpRange(trim($ip_range));
				$this->ip_address_array = array_merge($this->ip_address_array, $ip_range_obj->getIpAddressArray());
			}
			shuffle($this->ip_address_array);
		}
		return $this->ip_address_array;
	}
	
	/**
	 * Returns the hostname
	 * @return string
	 */
	function getHostname() {
		if (is_null($this->hostname)) {
			$this->hostname = trim(`hostname`);;
		}
		return $this->hostname;
	}
		
	/**
	 * Returns the verbose
	 * @return boolean
	 */
	function getVerbose() {
		if (is_null($this->verbose)) {
			$this->verbose = false;
		}
		return $this->verbose;
	}
	
	/**
	 * Sets the verbose
	 * @var boolean
	 */
	function setVerbose($arg0) {
		$this->verbose = $arg0;
		return $this;
	}
	
	/**
	 * Returns the test body to use for mailing
	 * @return string
	 */
	function getTestBody() {
		if (is_null($this->test_body)) {
			$header = 'EHLO ' . $this->getFromDomain() . "\n";
			$header .= 'MAIL FROM: <sue@' . $this->getFromDomain() . ">\n";
			$header .= 'RCPT TO: <' . $this->getSeedAccount() . ">\n";
			if ($this->getDisconnectEarly()) {
				$this->test_body = $header . "QUIT\n";
			} else {
				$header .= 'DATA' . "\n";
				$this->test_body = $header . "Hi mom, did you get the vacation pictures from me?\nJack\n.\nQUIT\n";
			}
		}
		return $this->test_body;
	}
	
	/**
	 * Returns the from_domain
	 * @return string
	 */
	function getFromDomain() {
		if (is_null($this->from_domain)) {
			$this->from_domain = "";		
		}
		return $this->from_domain;
	}
	
	/**
	 * Sets the from_domain
	 * @var string
	 */
	function setFromDomain($arg0) {
		$this->from_domain = $arg0;
		return $this;
	}
	
	/**
	 * Returns the seed_account
	 * @return string
	 */
	function getSeedAccount() {
		if (is_null($this->seed_account)) {
			$this->seed_account = "";
		}
		return $this->seed_account;
	}
	
	/**
	 * Sets the seed_account
	 * @var string
	 */
	function setSeedAccount($arg0) {
		$this->seed_account = $arg0;
		$this->discoverMxServer();
		return $this;
	}
	
	/**
	 * Returns the seed_account
	 * @return string
	 */
	function getSeedAccountDomain() {
		return substr($this->getSeedAccount(), strpos($this->getSeedAccount(), '@') + 1);
	}
	
	/**
	 * Returns the mx_server
	 * @return string
	 */
	function getMxServer() {
		if (is_null($this->mx_server)) {
			$this->mx_server = '';
		}
		return $this->mx_server;
	}
	
	/**
	 * Sets the mx_server
	 * @var string
	 */
	function setMxServer($arg0) {
		$this->mx_server = $arg0;
		return $this;
	}
	
	/**
	 * Discovers the mx server based on the seed account
	 * @return string
	 */
	function discoverMxServer() {
		// Calculate the MX record from the seed account
		$seed_domain = substr($this->getSeedAccount(), strpos($this->getSeedAccount(), '@') + 1);
		$mxhosts = array();
		getmxrr($this->getSeedAccountDomain(), $mxhosts);
		$mx_host = array_shift($mxhosts);
		$this->setMxServer($mx_host);
		return $this;
	}
	
	/**
	 * Returns the ethernet_interface
	 * @return string
	 */
	function getEthernetInterface() {
		if (is_null($this->ethernet_interface)) {
			$this->discoverEthernetInterface();
		}
		return $this->ethernet_interface;
	}
	
	/**
	 * Sets the ethernet_interface
	 * @var string
	 */
	function setEthernetInterface($arg0) {
		$this->ethernet_interface = $arg0;
		return $this;
	}
	
	/**
	 * Returns the randomize_ips
	 * @return boolean
	 */
	function getRandomizeIps() {
		if (is_null($this->randomize_ips)) {
			$this->randomize_ips = true;
		}
		return $this->randomize_ips;
	}
	
	/**
	 * Sets the randomize_ips
	 * @var boolean
	 */
	function setRandomizeIps($arg0) {
		$this->randomize_ips = $arg0;
		return $this;
	}
	
	/**
	 * Returns the use_pipelining
	 * @return boolean
	 */
	function getUsePipelining() {
		if (is_null($this->use_pipelining)) {
			$this->use_pipelining = true;
		}
		return $this->use_pipelining;
	}
	
	/**
	 * Sets the use_pipelining
	 * @var boolean
	 */
	function setUsePipelining($arg0) {
		$this->use_pipelining = $arg0;
		return $this;
	}
	
	/**
	 * Returns the disconnect_early
	 * @return boolean
	 */
	function getDisconnectEarly() {
		if (is_null($this->disconnect_early)) {
			$this->disconnect_early = false;
		}
		return $this->disconnect_early;
	}
	
	/**
	 * Sets the disconnect_early
	 * @var boolean
	 */
	function setDisconnectEarly($arg0) {
		$this->disconnect_early = $arg0;
		return $this;
	}
	
	/**
	 * Returns the ip_result_array
	 * @return array
	 */
	function getIpResultArray() {
		if (is_null($this->ip_result_array)) {
			$this->ip_result_array = array('good' => array(),
											'blocked' => array(),
											'in_use' => array(),
											'bad' => array(),
											'rdns' => array(),
											'timeout' => array()
			);
		}
		return $this->ip_result_array;
	}
	
	/**
	 * Sets the ip_result_array
	 * @var array
	 */
	function setIpResultArray($arg0) {
		$this->ip_result_array = $arg0;
		return $this;
	}
	
	/**
	 * Sets the ip_result_array
	 * @var array
	 */
	function addIpResult($ip_address, $result) {
		$ip_results = $this->getIpResultArray();
		if (!isset($ip_results[$result])) {
			$ip_results[$result] = array();
		}
		$ip_results[$result][] = $ip_address;
		
		$this->setIpResultArray($ip_results);
		return $this;
	}

	/**
	 * Discovers the default network interface
	 * @return DaoOffer_Form_IpTest
	 */
	function discoverEthernetInterface() {
		$interface = 'eth0';
		$cmd = '/sbin/ifconfig -s | awk \'{print $1}\'';
		$available_interfaces = explode("\n", shell_exec($cmd));
		array_shift($available_interfaces);
		$available_interfaces = array_flip($available_interfaces);
		if (isset($available_interfaces['eth0'])) {
			$this->setEthernetInterface('eth0');
		} else if (isset($available_interfaces['eth1'])) {
			$this->setEthernetInterface('eth1');
		}
		return $this;
	}
	
	/**
	 * Tests the IPs
	 * @return boolean
	 */
	function testIps() {
		$ip_addresses = $this->getIpAddressArray();
		if ($this->getRandomizeIps()) {
			$ip_addresses = array_slice($ip_addresses, 0, 10);
		
		}
		foreach ($ip_addresses as $ip_address) {
			$result = $this->testIp($ip_address);
			$this->addIpResult($ip_address, $result);
		}
	}
	
	/**
	 * Runs the IP Test
	 * @return string
	 */
	function testIp($ip_address) {
		$verbose_log = array();
		$color = StringTools::CONSOLE_COLOR_GREEN;
		$ret_val = 'good';
		try {
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Starting', StringTools::CONSOLE_COLOR_GREEN);
			
			// Verify that it is a valid ip address
			if (long2ip(sprintf("%u", ip2long(trim($ip_address)))) != trim($ip_address)) {
				$ret_val = 'bad';
				throw new Exception('Bad IP');
			}
			
			// Check if the IP is already bound to another box, if so, skip testing it
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Checking Usage', StringTools::CONSOLE_COLOR_RED);
			$cmd = 'arping -f -D ' . $ip_address . ' -c 1';
			$verbose_log[] = ">>> " . $cmd;
			if (($cmd_response = trim(shell_exec($cmd))) != '') {
				$verbose_log[] = "<<< " . implode("\n    ", explode("\n", $cmd_response));
				if (strpos($cmd_response, 'Received 1 response') !== false) {
					$ret_val = 'in_use';
					throw new Exception('Already In Use');
				}
			}
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Available', StringTools::CONSOLE_COLOR_GREEN);
			
			// Bind the IP to this box
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Binding IP', StringTools::CONSOLE_COLOR_RED);
			$cmd = 'ifconfig ' . $this->getEthernetInterface() . ':' . sprintf("%u", ip2long($ip_address)) . ' ' . $ip_address . ' netmask 255.255.255.0';
			$verbose_log[] = ">>> " . $cmd;
			shell_exec($cmd);
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Bound', StringTools::CONSOLE_COLOR_GREEN);
			
			// Issue an ARP request to tell the router we have this IP
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Updating ARP', StringTools::CONSOLE_COLOR_RED);
			$cmd = '/sbin/arping -f -c 1 -s ' . $ip_address . ' 8.8.8.8 2>&1 &';
			$verbose_log[] = ">>> " . $cmd;
			shell_exec($cmd);
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Updated', StringTools::CONSOLE_COLOR_GREEN);
			
			// Now format a message and send it to the seed account
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Sending Test', StringTools::CONSOLE_COLOR_RED);
			$temporary_name = tempnam('/tmp', 'iptest');
			$email = $this->getTestBody();
			$email_contents = str_ireplace('[[from_domain]]', $this->getSeedAccountDomain(), $email);
			$email_contents = str_ireplace('[[ip]]', $ip_address, $email_contents);
			$email_contents = str_ireplace('[HASH]', md5($this->getSeedAccount()), $email_contents);
			$email_contents = str_ireplace('[*to]', $this->getSeedAccount(), $email_contents);
			file_put_contents($temporary_name, $email_contents);
			if ($this->getUsePipelining()) {
				// Pipelining will send the entire message at once
				$cmd = 'cat ' . $temporary_name . ' | unix2dos | nc -v -w 1 -t -C -s ' . $ip_address . ' ' . $this->getMxServer() . ' 25';
			} else {
				// No pipelining will pause after each line
				$cmd = 'cat ' . $temporary_name . ' | unix2dos | nc -v -w 3 -i 1 -t -C -s ' . $ip_address . ' ' . $this->getMxServer() . ' 25';
			}
			$verbose_log[] = ">>> " . $cmd;
			$send_response = shell_exec($cmd);
			$verbose_log[] = ">>> " . implode("\n    ", file($temporary_name, FILE_IGNORE_NEW_LINES));
			$verbose_log[] = "<<< " . implode("\n    ", explode("\n", $send_response));
			
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Sent', StringTools::CONSOLE_COLOR_GREEN);
			
			// Now unbind the IP from the box
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Releasing IP', StringTools::CONSOLE_COLOR_RED);
			$cmd = 'ifconfig ' . $this->getEthernetInterface() . ':' . sprintf("%u", ip2long($ip_address)) . ' down';
			$verbose_log[] = ">>> " . $cmd;
			shell_exec($cmd);
			StringTools::consoleWrite('Testing IP ' . $ip_address, 'Released', StringTools::CONSOLE_COLOR_GREEN);
			
			// Now output our results to the screen
			if ($this->isBlocked($send_response)) {
				$color = StringTools::CONSOLE_COLOR_RED;
				$ret_val = 'blocked';
			} else if ($this->isBlockedRdns($send_response)) {
				$color = StringTools::CONSOLE_COLOR_CYAN;
				$ret_val = 'rdns';
			} else if ($this->isTimeout($send_response)) {
				$color = StringTools::CONSOLE_COLOR_CYAN;
				$ret_val = 'timeout';
			} else {
				$color = StringTools::CONSOLE_COLOR_GREEN;
				$ret_val = 'good';
			}
			
			StringTools::consoleWrite('Testing IP ' . $ip_address, $ret_val, $color, true);
			if ($this->getVerbose()) {
				echo "\n" . StringTools::consoleColor(implode("\n", $verbose_log), $color) . "\n";
			} else {
				echo "\n" . StringTools::consoleColor($send_response, $color) . "\n";
			}
		} catch (Exception $e) {
			if ($this->getVerbose()) { echo implode("\n", $verbose_log); }
			StringTools::consoleWrite('Testing IP ' . $ip_address, $e->getMessage(), StringTools::CONSOLE_COLOR_RED, true);
			//throw $e;
		}
		return $ret_val;
	}
	
	/**
	 * Checks if the result message is blocked
	 * @return boolean
	 */
	function isBlocked($message) {
		if (strpos(strtolower($message), 'denied') !== false ||
		strpos(strtolower($message), 'rejected') !== false ||
		strpos(strtolower($message), 'refused') !== false ||
		strpos(strtolower($message), 'blocked') !== false ||
		strpos(strtolower($message), 'banned') !== false ||
		strpos(strtolower($message), 'block list') !== false ||
		strpos(strtolower($message), 'spamhaus') !== false ||
		strpos(strtolower($message), '[ts02]') !== false ||
		strpos(strtolower($message), '[ts03]') !== false ||
		strpos(strtolower($message), 'rtr:bl') !== false ||
		strpos(strtolower($message), 'rly:bl') !== false ||
		strpos(strtolower($message), 'user complaints') !== false ||
		strpos(strtolower($message), 'suspicious activity') !== false
		) {
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if the result message is a timeout
	 * @return boolean
	 */
	function isBlockedRdns($message) {
		if (strpos(strtolower($message), 'host-not-in-DNS') !== false ||
		strpos(strtolower($message), 'invalid sender domain') !== false
		) {
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if the result message is a timeout
	 * @return boolean
	 */
	function isTimeout($message) {
		if (trim($message) == '') { return true; }
		return false;
	}
	
	/**
	 * Outputs the IP reults to the screen
	 * @return void
	 */
	function outputIpResults() {
		$result_array = $this->getIpResultArray();
		echo "\n\n";
		echo StringTools::consoleColor('IPs that are INVALID', StringTools::CONSOLE_COLOR_PURPLE) . "\n";
		echo StringTools::consoleColor(str_repeat('=', 27), StringTools::CONSOLE_COLOR_PURPLE) . "\n";
		if (isset($result_array['bad']) && is_array($result_array['bad'])) {
			foreach ($result_array['bad'] as $ip) {
				echo '  ' . StringTools::consoleColor($ip, StringTools::CONSOLE_COLOR_PURPLE) . "\n";
			}
		}
			
		echo "\n\n";
		echo StringTools::consoleColor('IPs already IN USE', StringTools::CONSOLE_COLOR_YELLOW) . "\n";
		echo StringTools::consoleColor(str_repeat('=', 27), StringTools::CONSOLE_COLOR_YELLOW) . "\n";
		if (isset($result_array['in_use']) && is_array($result_array['in_use'])) {
			foreach ($result_array['in_use'] as $ip) {
				echo '  ' . StringTools::consoleColor($ip, StringTools::CONSOLE_COLOR_YELLOW) . "\n";
			}
		}
			
		echo "\n\n";
		echo StringTools::consoleColor('IPs that TIMED OUT', StringTools::CONSOLE_COLOR_CYAN) . "\n";
		echo StringTools::consoleColor(str_repeat('=', 27), StringTools::CONSOLE_COLOR_CYAN) . "\n";
		if (isset($result_array['timeout']) && is_array($result_array['timeout'])) {
			foreach ($result_array['timeout'] as $ip) {
				echo '  ' . StringTools::consoleColor($ip, StringTools::CONSOLE_COLOR_CYAN) . "\n";
			}
		}
			
		echo "\n\n";
		echo StringTools::consoleColor('IPs that need rDNS', StringTools::CONSOLE_COLOR_BLUE) . "\n";
		echo StringTools::consoleColor(str_repeat('=', 27), StringTools::CONSOLE_COLOR_BLUE) . "\n";
		if (isset($result_array['rdns']) && is_array($result_array['rdns'])) {
			foreach ($result_array['rdns'] as $ip) {
				echo '  ' . StringTools::consoleColor($ip, StringTools::CONSOLE_COLOR_BLUE) . "\n";
			}
		}
			
		echo "\n\n";
		echo StringTools::consoleColor('IPs that are BLOCKED', StringTools::CONSOLE_COLOR_RED) . "\n";
		echo StringTools::consoleColor(str_repeat('=', 27), StringTools::CONSOLE_COLOR_RED) . "\n";
		if (isset($result_array['blocked']) && is_array($result_array['blocked'])) {
			foreach ($result_array['blocked'] as $ip) {
				echo '  ' . StringTools::consoleColor($ip, StringTools::CONSOLE_COLOR_RED) . "\n";
			}
		}
			
		echo "\n\n";
		echo StringTools::consoleColor('IPs that are GOOD', StringTools::CONSOLE_COLOR_GREEN) . "\n";
		echo StringTools::consoleColor(str_repeat('=', 27), StringTools::CONSOLE_COLOR_GREEN) . "\n";
		if (isset($result_array['good']) && is_array($result_array['good'])) {
			foreach ($result_array['good'] as $ip) {
				echo '  ' . StringTools::consoleColor($ip, StringTools::CONSOLE_COLOR_GREEN) . "\n";
			}
		}
			
		echo "\n\n";
		echo 'Summary Test Results' . "\n";
		echo 'Sent ' . count($this->getIpAddressArray()) . ' ips to ' . $this->getSeedAccount() . "\n";
		echo str_repeat('=', 27) . "\n";
		if (isset($result_array['good'])) { echo '  ' . StringTools::consoleColor(str_pad('Good IPs', 17, ' ', STR_PAD_RIGHT) . str_pad(count($result_array['good']), 6, ' ', STR_PAD_LEFT), StringTools::CONSOLE_COLOR_GREEN) . "\n"; }
		if (isset($result_array['rdns'])) { echo '  ' . StringTools::consoleColor(str_pad('Missing rDNS IPs', 17, ' ', STR_PAD_RIGHT) . str_pad(count($result_array['rdns']), 6, ' ', STR_PAD_LEFT), StringTools::CONSOLE_COLOR_BLUE) . "\n"; }
		if (isset($result_array['blocked'])) { echo '  ' . StringTools::consoleColor(str_pad('Blocked IPs', 17, ' ', STR_PAD_RIGHT) . str_pad(count($result_array['blocked']), 6, ' ', STR_PAD_LEFT), StringTools::CONSOLE_COLOR_RED) . "\n"; }
		if (isset($result_array['in_use'])) { echo '  ' . StringTools::consoleColor(str_pad('Unavailable IPs', 17, ' ', STR_PAD_RIGHT) . str_pad(count($result_array['in_use']), 6, ' ', STR_PAD_LEFT), StringTools::CONSOLE_COLOR_YELLOW) . "\n"; }
		if (isset($result_array['timeout'])) { echo '  ' . StringTools::consoleColor(str_pad('Timed out IPs', 17, ' ', STR_PAD_RIGHT) . str_pad(count($result_array['timeout']), 6, ' ', STR_PAD_LEFT), StringTools::CONSOLE_COLOR_CYAN) . "\n"; }
		if (isset($result_array['bad'])) { echo '  ' . StringTools::consoleColor(str_pad('Invalid IPs', 17, ' ', STR_PAD_RIGHT) . str_pad(count($result_array['bad']), 6, ' ', STR_PAD_LEFT), StringTools::CONSOLE_COLOR_PURPLE) . "\n"; }
	}
}

?>