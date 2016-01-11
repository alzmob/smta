<?php
namespace Smta;

class User extends Base\User {
	
	/**
	 * Updates the last login time for this user and it's account
	 * @return boolean
	 */
	function insert(array $criteria = array(), $hydrate = true) {
		$this->setToken(md5($this->getPassword() . strtotime('now')));
		return parent::insert($criteria, $hydrate);
	}
	
	/**
	 * Updates the last login time for this user and it's account
	 * @return boolean
	 */
	function updateLastLogin() {
		$this->setLastLoginTime(new \MongoDate());
		return parent::update();
	}
	
	/**
	 * Hashes the password before we save it
	 * @return void
	 */
	function hashPassword() {
		// This if very simply right now, but we can change it in the future
		$this->setPassword(md5($this->getPassword()));
		return $this;
	}
	
	/**
	 * performLogin queries a single row from the account_user table
	 * @param	Form
	 * @return	AccountUserForm
	 */
	public function tryLogin(){
		//Check the array exist or not
		$criteria = array();
		$critiera['username'] = $this->getUsername();
		
		$this->hashPassword();
		$criteria['password'] = $this->getPassword(); // Since we hashed the password, we can just pass this in as-is
		
		$ret_val = parent::query($criteria, false);
		
		if (!is_null($ret_val)) {
			$this->populate($ret_val);
			// Update the last login time
			$this->updateLastLogin();
			return $this;
		}
		throw new \Exception('The username and/or password does not match a user in the system');
	}
	
	/**
	 * Checks if the username already exists
	 * @return boolean
	 */
	function queryUsernameExists() {
		$criteria = array();
		$criteria['username'] = $this->getUsername();
		$criteria['_id'] = array('$ne' => $this->getId());
	
		$result = parent::count($criteria);
		return ($result > 0);
	}	
}