<?php
namespace Smta\Link;

class DropMapping extends BasicLink {
	
	protected $default_value;
	
	/**
	 * Returns the default_value
	 * @return string
	 */
	function getDefaultValue() {
		if (is_null($this->default_value)) {
			$this->default_value = "";
		}
		return $this->default_value;
	}
	
	/**
	 * Sets the default_value
	 * @var string
	 */
	function setDefaultValue($arg0) {
		$this->default_value = $arg0;
		$this->addModifiedColumn("default_value");
		return $this;
	}
}