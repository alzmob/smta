<?php
namespace Smta\Link;

class Ftp extends BasicLink {
	
	private $ftp;
	
	/**
	 * Returns the ftp
	 * @return \Smta\Ftp
	 */
	function getFtp() {
		if (is_null($this->ftp)) {
			$this->ftp = new \Smta\Ftp();
			$this->ftp->setId($this->getId());
			$this->ftp->query();
		}
		return $this->ftp;
	}
}