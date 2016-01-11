<?php
namespace Smta;

use \Mojavi\Form\CommonForm;

/**
 * IpRange takes an ip range string and converts it into an object allowing you to get information about the range
 * @author Mark Hobson
 */
class IpRange extends CommonForm {

	protected $ip_range;
	protected $domain;
	protected $range_prefix;
	protected $minimum_octet;
	protected $maximum_octet;
	protected $gateway;
	protected $broadcast;
	protected $cidr;
	protected $netmask;
	protected $ip_address_array;

	CONST SUBNET_29 = "29";
	CONST SUBNET_28 = "28";
	CONST SUBNET_27 = "27";
	CONST SUBNET_26 = "26";
	CONST SUBNET_25 = "25";
	CONST SUBNET_24 = "24";


	/**
	 * Returns the domain
	 * @return string
	 */
	function getDomain() {
		if (is_null($this->domain)) {
			$this->domain = "";
		}
		return $this->domain;
	}

	/**
	 * Sets the domain
	 * @param $arg0 string
	 */
	function setDomain($arg0) {
		$this->domain = $arg0;
		return $this;
	}

	/**
	 * Returns the ip_range
	 * @return string
	 */
	function getIpRange() {
		if (is_null($this->ip_range)) {
			$this->ip_range = '0.0.0.0';
		}
		return $this->ip_range;
	}

	/**
	 * Sets the ip_range
	 * @param $arg0 string
	 */
	function setIpRange($arg0) {
		$this->ip_range = trim($arg0);
		if (strpos($this->ip_range, " 255.255.255") !== false) {
			$matches = array();
			preg_match('/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3}) ([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/', $this->ip_range, $matches);
			if (isset($matches[1]) && isset($matches[2]) && isset($matches[3]) && isset($matches[4]) && isset($matches[5]) && isset($matches[6]) && isset($matches[7]) && isset($matches[8])) {
				// Build the CIDR from the last 4 octets
				$cidr = substr_count((string)decbin(ip2long($matches[5] . '.' . $matches[6] . '.' . $matches[7] . '.' . $matches[8])), '1');
				$this->setCidr($matches[1] . '.' . $matches[2] . '.' . $matches[3] . '.' . $matches[4] . '/' . $cidr);
				$this->calculateRangeSettings();
				$this->calculateIpArray();
			}
		} else if (strpos($this->ip_range, "-") !== false) {
			$matches = array();
			preg_match('/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})-([0-9]{1,3})/', $this->ip_range, $matches);
			if (isset($matches[1]) && isset($matches[2]) && isset($matches[3]) && isset($matches[4]) && isset($matches[5])) {
				$ip_difference = $matches[5] - $matches[4];
				if ($ip_difference <= 8) {
					$ip_difference = 8;
				} else if ($ip_difference <= 16) {
					$ip_difference = 16;
				} else if ($ip_difference <= 32) {
					$ip_difference = 32;
				} else if ($ip_difference <= 64) {
					$ip_difference = 64;
				} else if ($ip_difference <= 128) {
					$ip_difference = 128;
				} else if ($ip_difference > 128) {
					$ip_difference = 256;
				}
				$cidr = substr_count((string)decbin(ip2long('255.255.255.' . (256 - $ip_difference))), '1');
				$this->setCidr($matches[1] . '.' . $matches[2] . '.' . $matches[3] . '.' . $matches[4] . '/' . $cidr);
				$this->calculateRangeSettings();
				if ($matches[5] > substr($this->getBroadcast(), strrpos($this->getBroadcast(), '.') + 1)) {
					$this->setCidr($matches[1] . '.' . $matches[2] . '.' . $matches[3] . '.' . $matches[4] . '/' . ($cidr - 1));
					$this->calculateRangeSettings();
				}
				$this->setMinimumOctet($matches[4]);
				$this->setMaximumOctet($matches[5]);
				$this->calculateIpArray();
			}
		} else if (strpos($this->ip_range, "/") !== false) {
			$matches = array();
			preg_match('/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\/([0-9]{1,2})/', $this->ip_range, $matches);
			if (isset($matches[1]) && isset($matches[2]) && isset($matches[3]) && isset($matches[4]) && isset($matches[5])) {
				$this->setCidr($this->ip_range);
				$this->calculateRangeSettings();
				$this->calculateIpArray();
			}
		} else {
			$matches = array();
			preg_match('/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/', $this->ip_range, $matches);
			if (isset($matches[1]) && isset($matches[2]) && isset($matches[3]) && isset($matches[4])) {
				$this->setCidr($this->ip_range . '/32');
				$this->calculateRangeSettings();
				$this->calculateIpArray();
			}
		}
		return $this;
	}

	/**
	 * Calculates the gateway, netmask, cidr and broadcast from an ip range
	 * @return DaoOffer_Form_IpRange
	 */
	function calculateRangeSettings() {
		// Calculate the netmask from the ip difference
		$netmask = long2ip(bindec(str_pad(str_repeat('1', substr($this->getCidr(), strrpos($this->getCidr(), '/') + 1)), 32, '0', STR_PAD_RIGHT)));
		$this->setNetmask($netmask);
		// Calculate the binary representation of the netmask, first ip and last ip
		$netmask_bin = decbin(ip2long($netmask));
		$first = substr($this->getCidr(), 0, strpos($this->getCidr(), '/'));
		$first_bin = (str_pad(decbin(ip2long($first)), 32, "0", STR_PAD_LEFT) & $netmask_bin);
		$last_bin = '';
		for ($i = 0; $i < 32; $i++) {
			$last_bin .= ($netmask_bin[$i] == "1") ? $first_bin[$i] : "1";
		}
		// Count the # of 1's in the netmask for the cidr
		$cidr = substr_count((string)$netmask_bin, '1');
		$this->setCidr(long2ip(bindec($first_bin)) . '/' . $cidr);
		$this->setBroadcast(long2ip(bindec($last_bin)));
		if (ip2long($this->getBroadcast()) > (bindec($first_bin) + 1)) {
			$this->setGateway(long2ip(bindec($first_bin) + 1));
		} else {
			$this->setGateway(long2ip(bindec($first_bin)));
		}
		if (ip2long($this->getGateway()) <= (ip2long($this->getBroadcast()) - 1)) {
			$this->setMaximumOctet(substr(long2ip(ip2long($this->getBroadcast()) - 1), strrpos(long2ip(ip2long($this->getBroadcast()) - 1), '.') + 1));
		} else {
			$this->setMaximumOctet(substr(long2ip(ip2long($this->getBroadcast())), strrpos(long2ip(ip2long($this->getBroadcast())), '.') + 1));
		}
		if (ip2long($this->getBroadcast()) > (ip2long($this->getGateway()) + 1)) {
			$this->setMinimumOctet(substr(long2ip(ip2long($this->getGateway()) + 1), strrpos(long2ip(ip2long($this->getGateway()) + 1), '.') + 1));
		} else {
			$this->setMinimumOctet(substr(long2ip(ip2long($this->getGateway())), strrpos(long2ip(ip2long($this->getGateway())), '.') + 1));
		}
		$prefix_array = explode(".", $this->getGateway());
		array_pop($prefix_array);
		$this->setRangePrefix(implode(".", $prefix_array));
		return $this;
	}

	/**
	 * Calculates the available ips on a range
	 * @return DaoOffer_Form_IpRange
	 */
	function calculateIpArray() {
		// Calculate the netmask from the ip difference
		$this->setIpAddressArray(null);
		for ($i=$this->getMinimumOctet();$i<=$this->getMaximumOctet();$i++) {
			$this->addIpAddressArray($this->getRangePrefix() . '.' . $i);
		}
		return $this;
	}

	/**
	 * Returns the range_prefix
	 * @return string
	 */
	function getRangePrefix() {
		if (is_null($this->range_prefix)) {
			$this->range_prefix = "";
		}
		return $this->range_prefix;
	}

	/**
	 * Sets the range_prefix
	 * @param $arg0 string
	 */
	function setRangePrefix($arg0) {
		$this->range_prefix = $arg0;
		return $this;
	}

	/**
	 * Returns the minimum_octet
	 * @return integer
	 */
	function getMinimumOctet() {
		if (is_null($this->minimum_octet)) {
			$this->minimum_octet = 2;
		}
		return $this->minimum_octet;
	}

	/**
	 * Sets the minimum_octet
	 * @param $arg0 integer
	 */
	function setMinimumOctet($arg0) {
		$this->minimum_octet = $arg0;
		return $this;
	}

	/**
	 * Returns the maximum_octet
	 * @return integer
	 */
	function getMaximumOctet() {
		if (is_null($this->maximum_octet)) {
			$this->maximum_octet = 254;
		}
		return $this->maximum_octet;
	}

	/**
	 * Sets the maximum_octet
	 * @param $arg0 integer
	 */
	function setMaximumOctet($arg0) {
		$this->maximum_octet = $arg0;
		return $this;
	}

	/**
	 * Returns the gateway
	 * @return string
	 */
	function getGateway() {
		if (is_null($this->gateway)) {
			$this->gateway = "";
		}
		return $this->gateway;
	}

	/**
	 * Sets the gateway
	 * @param $arg0 string
	 */
	function setGateway($arg0) {
		$this->gateway = $arg0;
		return $this;
	}

	/**
	 * Returns the broadcast
	 * @return string
	 */
	function getBroadcast() {
		if (is_null($this->broadcast)) {
			$this->broadcast = "";
		}
		return $this->broadcast;
	}

	/**
	 * Sets the broadcast
	 * @param $arg0 string
	 */
	function setBroadcast($arg0) {
		$this->broadcast = $arg0;
		return $this;
	}

	/**
	 * Returns the range name
	 * @return string
	 */
	function getRange() {
		return $this->getRangePrefix() . '.' . $this->getMinimumOctet() . '-' . $this->getMaximumOctet();
	}

	/**
	 * Returns subnets based on the range
	 * @return array
	 */
	function getSubnets($subnet_option) {
		$subnets = array();
		if ($subnet_option == self::SUBNET_25) {
			for ($i=0;$i<2;$i++) {
				$subnets[] = $this->getRangePrefix() . '.' . ($i * 128) . '/25';
			}
		} else if ($subnet_option == self::SUBNET_26) {
			for ($i=0;$i<4;$i++) {
				$subnets[] = $this->getRangePrefix() . '.' . ($i * 64) . '/26';
			}
		} else if ($subnet_option == self::SUBNET_27) {
			for ($i=0;$i<8;$i++) {
				$subnets[] = $this->getRangePrefix() . '.' . ($i * 32) . '/27';
			}
		} else if ($subnet_option == self::SUBNET_28) {
			for ($i=0;$i<16;$i++) {
				$subnets[] = $this->getRangePrefix() . '.' . ($i * 16) . '/28';
			}
		} else if ($subnet_option == self::SUBNET_29) {
			for ($i=0;$i<32;$i++) {
				$subnets[] = $this->getRangePrefix() . '.' . ($i * 8) . '/29';
			}
		}
		$ret_val = array();
		foreach ($subnets as $subnet) {
			$parsed_subnet = new \Smta\IpRange();
			$parsed_subnet->setIpRange($subnet);
			$ret_val[] = $parsed_subnet;
		}
		return $ret_val;
	}

	/**
	 * Returns the cidr
	 * @return string
	 */
	function getCidr() {
		if (is_null($this->cidr)) {
			$this->cidr = "/24";
		}
		return $this->cidr;
	}

	/**
	 * Sets the cidr
	 * @param $arg0 string
	 */
	function setCidr($arg0) {
		$this->cidr = $arg0;
		return $this;
	}

	/**
	 * Returns the netmask
	 * @return string
	 */
	function getNetmask() {
		if (is_null($this->netmask)) {
			$this->netmask = "255.255.255.0";
		}
		return $this->netmask;
	}

	/**
	 * Sets the netmask
	 * @param $arg0 string
	 */
	function setNetmask($arg0) {
		$this->netmask = $arg0;
		return $this;
	}

	/**
	 * Returns the ip_address_array
	 * @return array
	 */
	function getIpAddressArray() {
		if (is_null($this->ip_address_array)) {
			$this->ip_address_array = array();
		}
		return $this->ip_address_array;
	}
	/**
	 * Sets the ip_address_array
	 * @param array
	 */
	function setIpAddressArray($arg0) {
		if (is_array($arg0)) {
			$this->ip_address_array = $arg0;
		} else if (is_string($arg0)) {
			$this->ip_address_array = explode("\n", $arg0);
		}
		return $this;
	}
	/**
	 * Sets the ip_address_array
	 * @param array
	 */
	function addIpAddressArray($arg0) {
		$tmp_array = $this->getIpAddressArray();
		$tmp_array[] = $arg0;
		$this->setIpAddressArray($tmp_array);
		return $this;
	}

	/**
	 * Parses and IP range into an array of it's parts
	 * @param string $arg0
	 * @return array
	 */
	public static function parseIpRange($arg0) {
		$ip_range = new \Smta\IpRange();
		$ip_range->setIpRange($arg0);
		return $ip_range;
	}

	/**
	 * Binds the range to this box
	 * @return boolean
	 */
	static function bindRange($range) {
		$ip_range = new \Smta\IpRange();
		$ip_range->setIpRange($range);

		$ip_address_array = $ip_range->getIpAddressArray();
		if (count($ip_address_array) > 2) {
			$first_ip = array_shift($ip_address_array);
			$last_ip = array_pop($ip_address_array);

			$label_start = sprintf("%u", ip2long($first_ip));
			$label_end = sprintf("%u", ip2long($last_ip));

			// Now create the ifcfg file
			$file_contents = array();
			$file_contents[] = 'IPADDR_START=' . $first_ip;
			$file_contents[] = 'IPADDR_END=' . $last_ip;
			$file_contents[] = 'CLONENUM_START=' . $label_start;
			$file_contents[] = 'ONBOOT=yes';
			$file_contents[] = 'ARPCHECK=no';
			if (file_exists(MO_WEBAPP_DIR . '/meta/crons/move_eth_file.sh')) {
				// If we have the appropriate script, then use that one with sudo
				file_put_contents('/tmp/ifcfg-eth0-range' . $label_start . '-' . $label_end, implode("\n", $file_contents));
				$cmd = 'sudo ' . MO_WEBAPP_DIR . '/meta/crons/move_eth_file.sh move ' . '/tmp/ifcfg-eth0-range' . $label_start . '-' . $label_end;
				shell_exec($cmd);
			} else {
				file_put_contents('/etc/sysconfig/network-scripts/ifcfg-eth0-range' . $label_start . '-' . $label_end, implode("\n", $file_contents));
			}
		} else {
			foreach ($ip_range->getIpAddressArray() as $ip_address) {
				self::bindIp($ip_address);
			}
		}
	}

	/**
	 * Binds an ip address to this box
	 * @param $ip_address
	 * @return boolean
	 */
	static function bindIp($ip_address) {
		$label = sprintf("%u", ip2long($ip_address));

		// Issue the ip addr add command to bind the ip to this box
		$cmd = 'sudo /sbin/ip addr add ' . $ip_address . '/32 dev eth0 label eth0:' . $label . ' 2>&1 &';
		shell_exec($cmd);
		// Issue the arping command to update the routing table
		$cmd = 'sudo /sbin/arping -f -c 1 -s ' . $ip_address . ' 4.2.2.2';
		shell_exec($cmd);
		// Now create the ifcfg file
		$file_contents = array();
		$file_contents[] = 'DEVICE=eth0:' . $label;
		$file_contents[] = 'BOOTPROTO=static';
		$file_contents[] = 'IPADDR=' . $ip_address;
		$file_contents[] = 'NETMASK=255.255.255.255';
		$file_contents[] = 'ONBOOT=yes';
		$file_contents[] = 'ARPCHECK=no';
		if (file_exists(MO_WEBAPP_DIR . '/meta/crons/move_eth_file.sh')) {
			// If we have the appropriate script, then use that one with sudo
			file_put_contents('/tmp/ifcfg-eth0:' . $label, implode("\n", $file_contents));
			$cmd = 'sudo ' . MO_WEBAPP_DIR . '/meta/crons/move_eth_file.sh move ' . '/tmp/ifcfg-eth0:' . $label;
			shell_exec($cmd);
		} else {
			file_put_contents('/etc/sysconfig/network-scripts/ifcfg-eth0:' . $label, implode("\n", $file_contents));
		}
		return true;
	}

	/**
	 * Unbinds the range from this box
	 * @return boolean
	 */
	static function unbindRange($ip_range) {
		$ip_range = new \Smta\IpRange();
		$ip_range->setIpRange($ip_range);
		foreach ($ip_range->getIpAddressArray() as $ip_address) {
			self::unbindIp($ip_address, false);
		}
	}

	/**
	 * Unbinds a single ip from this box
	 * @param $ip_address
	 * @return boolean
	 */
	static function unbindIp($ip_address) {
		$label = sprintf("%u", ip2long($ip_address));
		// Issue the ifconfig down command to take down this ip address
		$cmd = 'sudo /sbin/ifconfig eth0:' . $label . ' down 2>&1 &';
		shell_exec($cmd);
		// Remove the ifcfg file
		if (file_exists('/etc/sysconfig/network-scripts/ifcfg-eth0:' . $label)) {
			if (file_exists(MO_WEBAPP_DIR . '/meta/crons/move_eth_file.sh')) {
				$cmd = 'sudo ' . MO_WEBAPP_DIR . '/meta/crons/move_eth_file.sh remove /tmp/ifcfg-eth0:' . $label;
				shell_exec($cmd);
			} else {
				$cmd = 'rm -f /etc/sysconfig/network-scripts/ifcfg-eth0:' . $label;
				shell_exec($cmd);
			}
		}
		// Find any range files that may have this IP
		$range_files = glob('/etc/sysconfig/network-scripts/ifcfg-eth0-range*');
		foreach ($range_files as $range_file) {
			$range_array = explode("-", str_replace('ifcfg-eth0-range', '', $range_file));
			if ($range_array[0] < $label && $range_array[1] > $label) {
				// This is the range file, so remove it
				if (file_exists(MO_WEBAPP_DIR . '/meta/crons/move_eth_file.sh')) {
					$cmd = 'sudo ' . MO_WEBAPP_DIR . '/meta/crons/move_eth_file.sh remove /tmp/' . $range_file;
					shell_exec($cmd);
				} else {
					$cmd = 'rm -f /etc/sysconfig/network-scripts/' . basename($range_file);
					shell_exec($cmd);
				}
			}
		}
		return true;
	}

	/**
	 * Checks that an IP address is not bound elsewhere on the network
	 * @param string $ip_address
	 * @return boolean
	 */
	public static function isIpInUse($ip_address) {
		$cmd = 'sudo /sbin/arping -f -D ' . $ip_address . ' -c 1';
		if (($cmd_response = trim(shell_exec($cmd))) != '') {
			if (strpos($cmd_response, 'Received 1 response') !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks that an IP address is bound to this box
	 * @param string $ip_address
	 * @return boolean
	 */
	public static function isIpBound($ip_address) {
		$cmd = '/sbin/ip addr show | /bin/grep "' . $ip_address . '/" | wc -l';
		$ret_val = trim(shell_exec($cmd));
		return ($ret_val == '1');
	}

	/**
	 * Returns a list of server ips bound to this box
	 * @return array
	 */
	public static function getServerIps() {
		$ret_val = array();
		$cmd = '/sbin/ifconfig | /bin/grep "eth0 " -A 1 | /bin/grep "inet addr" | /bin/awk \'{print $2}\'';
		$primary_ip = trim(shell_exec($cmd));
		if (strpos($primary_ip, 'addr:') === 0) {
			$primary_ip = substr($primary_ip, strlen('addr:'));
		}

		$cmd = '/sbin/ifconfig | /bin/grep "eth1 " -A 1 | /bin/grep "inet addr" | /bin/awk \'{print $2}\'';
		$secondary_ip = trim(shell_exec($cmd));
		if (strpos($secondary_ip, 'addr:') === 0) {
			$secondary_ip = substr($secondary_ip, strlen('addr:'));
		}

		$cmd = '/sbin/ifconfig | /bin/grep "inet addr" | /bin/awk \'{print $2}\'';
		$ip_lines = explode("\n", trim(shell_exec($cmd)));
		foreach ($ip_lines as $ip_line) {
			$ip_address = substr($ip_line, strlen('addr:'));
			if ($ip_address == '127.0.0.1') { continue; }
			if ($ip_address == $primary_ip) { continue; }
			if ($ip_address == $secondary_ip) { continue; }
			$ret_val[] = $ip_address;
		}
		return $ret_val;
	}

	/**
	 * Returns a list of server ips bound to this box
	 * @return array
	 */
	public static function getServerIpsAsLong() {
		$ret_val = array();
		foreach (self::getServerIps() as $ip_address) {
			$ret_val[] = sprintf("%u", ip2long($ip_address));
		}
		return $ret_val;
	}

	/**
	 * Returns a list of server ip ranges bound to this box
	 * @return array
	 */
	public static function getServerIpRanges() {
		$ret_val = array();
		$ips = self::getServerIpsAsLong();
		$next_ip = '';
		asort($ips);
		$current_ip_array = array();
		foreach ($ips as $key => $ip) {
			if ($ip == '0') { continue; }
			if (trim($ip) == '') { continue; }
			$ip = long2ip($ip);
			if ($ip == '127.0.0.1') { continue; }
			$ip_prefix = substr($ip, 0, strrpos($ip, "."));
			$ip_octet = substr($ip, strrpos($ip, ".") + 1);
			if ($ip != $next_ip) {
				$ret_val[] = $current_ip_array;
				$current_ip_array = array();
				$current_ip_array['prefix'] = $ip_prefix;
				$current_ip_array['min'] = $ip_octet;
				$current_ip_array['max'] = $ip_octet;
			} else {
				$current_ip_array['max'] = $ip_octet;
			}
			$next_ip = $ip_prefix . '.' . ($ip_octet + 1);
		}
		$ret_val[] = $current_ip_array;
		return $ret_val;
	}
	
	/**
	 * Returns a list of server ip ranges bound to this box
	 * @return array
	 */
	public static function getIpsToIpRanges($ip_address_array) {
		$ips = array();
		foreach ($ip_address_array as $ip_address) {
			$ips[] = sprintf("%u", ip2long($ip_address));
		}
		$ret_val = array();
		$last_ip_prefix = '';
		asort($ips);
		$current_ip_array = array();
		foreach ($ips as $key => $ip) {
			if ($ip == '0') { continue; }
			if (trim($ip) == '') { continue; }
			$ip = long2ip($ip);
			if ($ip == '127.0.0.1') { continue; }
			$ip_prefix = substr($ip, 0, strrpos($ip, "."));
			$ip_octet = substr($ip, strrpos($ip, ".") + 1);
			if ($ip_prefix != $last_ip_prefix) {
				if (count($current_ip_array) > 0) {
					$ret_val[] = $current_ip_array;
				}
				$current_ip_array = array();
				$last_ip_prefix = $ip_prefix;
				$current_ip_array['prefix'] = $ip_prefix;
				$current_ip_array['min'] = $ip_octet;
				$current_ip_array['max'] = $ip_octet;
			} else {
				$current_ip_array['max'] = $ip_octet;
			}
		}
		if (count($current_ip_array) > 0) {
			$ret_val[] = $current_ip_array;
		}
		foreach ($ret_val as $key => $ip_array) {
			if (isset($ip_array['prefix']) && isset($ip_array['min']) && isset($ip_array['max'])) {
				$ip_settings = self::parseIpRange($ip_array['prefix'] . '.' . $ip_array['min'] . '-' . $ip_array['max']);
			}
			$ret_val[$key]['cidr'] = $ip_settings->getCidr();
		}
		
		return $ret_val;
	}

	/**
	 * Returns the server hostname
	 * @return string
	 */
	static function getServerHostname() {
		return trim(shell_exec('hostname'));
	}

}
?>