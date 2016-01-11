<?php
namespace Smta\Link;

class DropStats extends BasicLink {
	
	protected $list_size;
	protected $queue_size;
	protected $deliverd_size;
	protected $bounce_size;
	
	protected $drop_start_time;
	protected $drop_end_time;
	
	/**
	 * Resets the reporting stats
	 * @return boolean
	 */
	function resetReportingStats() {
		$this->list_size = 0;
		$this->queue_size = 0;
		$this->delivered_size = 0;
		$this->bounce_size = 0;
		$this->drop_start_time = new \MongoDate();
		$this->drop_end_time = new \MongoDate();
	}
	
	/**
	 * Returns the list_size
	 * @return integer
	 */
	function getListSize() {
		if (is_null($this->list_size)) {
			$this->list_size = 0;
		}
		return $this->list_size;
	}
	
	/**
	 * Sets the list_size
	 * @var integer
	 */
	function setListSize($arg0) {
		$this->list_size = (int)$arg0;
		return $this;
	}
	
	/**
	 * Returns the queue_size
	 * @return integer
	 */
	function getQueueSize() {
		if (is_null($this->queue_size)) {
			$this->queue_size = 0;
		}
		return $this->queue_size;
	}
	
	/**
	 * Sets the queue_size
	 * @var integer
	 */
	function setQueueSize($arg0) {
		$this->queue_size = (int)$arg0;
		return $this;
	}
	
	/**
	 * Returns the delivered_size
	 * @return integer
	 */
	function getDeliveredSize() {
		if (is_null($this->delivered_size)) {
			$this->delivered_size = 0;
		}
		return $this->delivered_size;
	}
	
	/**
	 * Sets the delivered_size
	 * @var integer
	 */
	function setDeliveredSize($arg0) {
		$this->delivered_size = (int)$arg0;
		return $this;
	}
	
	/**
	 * Returns the bounce_size
	 * @return integer
	 */
	function getBounceSize() {
		if (is_null($this->bounce_size)) {
			$this->bounce_size = 0;
		}
		return $this->bounce_size;
	}
	
	/**
	 * Sets the bounce_size
	 * @var integer
	 */
	function setBounceSize($arg0) {
		$this->bounce_size = (int)$arg0;
		return $this;
	}
	
	/**
	 * Returns the drop_start_time
	 * @return \MongoDate
	 */
	function getDropStartTime() {
		if (is_null($this->drop_start_time)) {
			$this->drop_start_time = new \MongoDate();
		}
		return $this->drop_start_time;
	}
	
	/**
	 * Sets the drop_start_time
	 * @var \MongoDate
	 */
	function setDropStartTime($arg0) {
		if ($arg0 instanceof \MongoDate) {
			$this->drop_start_time = $arg0;
		} else if (is_string($arg0)) {
			$this->drop_start_time = new \MongoDate(strtotime($arg0));
		} else if (is_int($arg0)) {
			$this->drop_start_time = new \MongoDate($arg0);
		}
		return $this;
	}
	
	/**
	 * Returns the drop_end_time
	 * @return \MongoDate
	 */
	function getDropEndTime() {
		if (is_null($this->drop_end_time)) {
			$this->drop_end_time = new \MongoDate();
		}
		return $this->drop_end_time;
	}
	
	/**
	 * Sets the drop_end_time
	 * @var \MongoDate
	 */
	function setDropEndTime($arg0) {
		if ($arg0 instanceof \MongoDate) {
			$this->drop_end_time = $arg0;
		} else if (is_string($arg0)) {
			$this->drop_end_time = new \MongoDate(strtotime($arg0));
		} else if (is_int($arg0)) {
			$this->drop_end_time = new \MongoDate($arg0);
		}
		return $this;
	}
	
	
}