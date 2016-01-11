<?php
namespace Smta\Link;

class User extends BasicLink {
	
	private $user;
	
	/**
	 * Returns the user
	 * @return \Smta\User
	 */
	function getUser() {
		if (is_null($this->user)) {
			$this->user = new \Smta\User();
			$this->user->setId($this->getId());
			$this->user->query();
		}
		return $this->user;
	}
}