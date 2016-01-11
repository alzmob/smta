<?php
namespace Smta\Base;

use \Mojavi\Form\MongoForm;

class User extends MongoForm {
    
	protected $name;
	protected $username;
	protected $password;
	protected $email;
	protected $active;
	protected $last_login_time;
	protected $ftp;
	protected $token;
	
	/**
	 * Constructs new user
	 * @return void
	 */
	function __construct() {
		$this->setCollectionName('user');
		$this->setDbName('default');
	}
	
	/**
	 * Returns the name
	 * @return string
	 */
	function getName() {
		if (is_null($this->name)) {
			$this->name = "";
		}
		return $this->name;
	}
	
	/**
	 * Sets the name
	 * @var string
	 */
	function setName($arg0) {
		$this->name = $arg0;
		$this->addModifiedColumn("name");
		return $this;
	}
	
	/**
	 * Returns the username
	 * @return string
	 */
	function getUsername() {
		if (is_null($this->username)) {
			$this->username = "";
		}
		return $this->username;
	}
	
	/**
	 * Sets the username
	 * @var string
	 */
	function setUsername($arg0) {
		$this->username = $arg0;
		$this->addModifiedColumn("username");
		return $this;
	}
	
	/**
	 * Returns the password
	 * @return string
	 */
	function getPassword() {
		if (is_null($this->password)) {
			$this->password = "";
		}
		return $this->password;
	}
	
	/**
	 * Sets the password
	 * @var string
	 */
	function setPassword($arg0) {
		$this->password = $arg0;
		$this->addModifiedColumn("password");
		return $this;
	}
	
	/**
	 * Returns the token
	 * @return string
	 */
	function getToken() {
		if (is_null($this->token)) {
			$this->token = "";
		}
		return $this->token;
	}
	
	/**
	 * Sets the token
	 * @var string
	 */
	function setToken($arg0) {
		$this->token = $arg0;
		$this->addModifiedColumn("token");
		return $this;
	}
	
	/**
	 * Returns the email
	 * @return string
	 */
	function getEmail() {
		if (is_null($this->email)) {
			$this->email = "";
		}
		return $this->email;
	}
	
	/**
	 * Sets the email
	 * @var string
	 */
	function setEmail($arg0) {
		$this->email = $arg0;
		$this->addModifiedColumn("email");
		return $this;
	}
	
	/**
	 * Returns the active
	 * @return boolean
	 */
	function getActive() {
		if (is_null($this->active)) {
			$this->active = false;
		}
		return $this->active;
	}
	
	/**
	 * Sets the active
	 * @var boolean
	 */
	function setActive($arg0) {
		$this->active = $arg0;
		$this->addModifiedColumn("active");
		return $this;
	}
	
	/**
	 * Returns the last_login_time
	 * @return \MongoDate
	 */
	function getLastLoginTime() {
		if (is_null($this->last_login_time)) {
			$this->last_login_time = new \MongoDate();
		}
		return $this->last_login_time;
	}
	
	/**
	 * Sets the last_login_time
	 * @var \MongoDate
	 */
	function setLastLoginTime($arg0) {
		$this->last_login_time = $arg0;
		$this->addModifiedColumn("last_login_time");
		return $this;
	}
	
	/**
	 * Returns the ftp
	 * @return \Smta\Link\Ftp
	 */
	function getFtp() {
		if (is_null($this->ftp)) {
			$this->ftp = new \Smta\Link\Ftp();
		}
		return $this->ftp;
	}
	
	/**
	 * Sets the ftp
	 * @var \Smta\Link\Ftp
	 */
	function setFtp($arg0) {
		if (is_array($arg0)) {
			$this->ftp = new \Smta\Link\Ftp();
			$this->ftp->populate($arg0);
			if ($this->ftp->getName() == '') {
				$this->setName($this->ftp->getFtp()->getUsername());
			}			
		} else if (is_string($arg0) && \MongoId::isValid($arg0)) {
			$this->ftp = new \Smta\Link\Ftp();
			$this->ftp->setId($arg0);
			if ($this->ftp->getName() == '') {
				$this->setName($this->ftp->getFtp()->getUsername());
			}
		} else if ($arg0 instanceof \MongoId) {
			$this->ftp = new \Smta\Link\Ftp();
			$this->ftp->setId($arg0);
			if ($this->ftp->getName() == '') {
				$this->setName($this->ftp->getFtp()->getUsername());
			}
		}
		$this->addModifiedColumn("ftp");
		return $this;
	}
	
	
}