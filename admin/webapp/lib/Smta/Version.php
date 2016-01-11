<?php
namespace Smta;

class Version extends CommonForm {
	
	protected $version;
	
	/**
	 * Returns the version
	 * @return string
	 */
	function getVersion() {
		if (is_null($this->version)) {
			$this->version = \Smta\Setting::getSetting('VERSION');
		}
		return $this->version;
	}
	
	/**
	 * Sets the version
	 * @var string
	 */
	function setVersion($arg0) {
		$this->version = $arg0;
		$this->addModifiedColumn("version");
		return $this;
	}	
}