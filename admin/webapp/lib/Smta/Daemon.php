<?php
namespace Smta;

class Daemon extends Base\Daemon {
	
	// +------------------------------------------------------------------------+
	// | CONSTANTS																|
	// +------------------------------------------------------------------------+
	
	// +------------------------------------------------------------------------+
	// | PRIVATE VARIABLES														|
	// +------------------------------------------------------------------------+
	protected $is_running;
	// +------------------------------------------------------------------------+
	// | PUBLIC METHODS															|
	// +------------------------------------------------------------------------+
	
	// +------------------------------------------------------------------------+
	// | RELATION METHODS														|
	// +------------------------------------------------------------------------+

	// +------------------------------------------------------------------------+
	// | HELPER METHODS															|
	// +------------------------------------------------------------------------+
	/**
	 * Returns the is_running
	 * @return boolean
	 */
	function getIsRunning() {
		if (is_null($this->is_running)) {
			if (intval($this->getPid()) > 0) {
				$out = shell_exec('ps ' . $this->getPid() . ' | wc -l');
				$this->is_running = ($out >= 2);
			} else {
				$this->is_running = false;
			}
		}
		return $this->is_running;
	}
	
	/**
	 * Queries for a data field by the name
	 * @return Daemon
	 */
	function queryByName() {
		$criteria = array('name' => $this->getName());
		return parent::query($criteria, false);
	}
	
	/**
	 * Queries for a data field by the name
	 * @return Daemon
	 */
	function queryByType() {
		$criteria = array('type' => $this->getType());
		return parent::query($criteria, false);
	}
	
	/**
	 * Queries for a data field by the class name
	 * @return Daemon
	 */
	function queryByClass() {
		$criteria = array('class_name' => $this->getClassName());
		return parent::query($criteria, false);
	}
}