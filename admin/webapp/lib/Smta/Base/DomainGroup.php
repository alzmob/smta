<?php
namespace Smta\Base;

use Mojavi\Form\MongoForm;
/**
 * DomainGroup contains methods to work with the account table.
 * Stores a list of accounts in the system 
 * @author	 
 * @since 12/30/2013 4:39 pm 
 */
class DomainGroup extends MongoForm {
	
	// +------------------------------------------------------------------------+
	// | PRIVATE VARIABLES														|
	// +------------------------------------------------------------------------+
	
	protected $name;
	protected $description;
	protected $domains;
	protected $is_gi_default;
	protected $color;
	protected $email_count;
	protected $use_global_suffixes;
	
	// +------------------------------------------------------------------------+
	// | CONSTRUCTOR															|
	// +------------------------------------------------------------------------+
	/**
	 * Constructs a new object
	 * @return Account
	 */
	function __construct() {
		$this->setCollectionName('domain_group');
		$this->setDbName('default');
	}
	
	// +------------------------------------------------------------------------+
	// | PUBLIC METHODS															|
	// +------------------------------------------------------------------------+
	
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
		$this->addModifiedColumn('name');
		return $this;
	}
	
	/**
	 * Returns the description
	 * @return string
	 */
	function getDescription() {
		if (is_null($this->description)) {
			$this->description = "";
		}
		return $this->description;
	}
	
	/**
	 * Sets the description
	 * @var string
	 */
	function setDescription($arg0) {
		$this->description = $arg0;
		$this->addModifiedColumn('description');
		return $this;
	}
	
	/**
	 * Returns the use_global_suffixes
	 * @return boolean
	 */
	function getUseGlobalSuffixes() {
		if (is_null($this->use_global_suffixes)) {
			$this->use_global_suffixes = false;
		}
		return $this->use_global_suffixes;
	}
	
	/**
	 * Sets the use_global_suffixes
	 * @var boolean
	 */
	function setUseGlobalSuffixes($arg0) {
		$this->use_global_suffixes = (boolean)$arg0;
		$this->addModifiedColumn("use_global_suffixes");
		return $this;
	}
	
	/**
	 * Returns the color
	 * @return integer
	 */
	function getColor() {
		if (is_null($this->color)) {
			$this->color = "#000000";
		}
		return $this->color;
	}
	
	/**
	 * Sets the color
	 * @var integer
	 */
	function setColor($arg0) {
		$this->color = $arg0;
		$this->addModifiedColumn('color');
		return $this;
	}
	
	/**
	 * Returns the domains
	 * @return string
	 */
	function getDomains() {
		if (is_null($this->domains)) {
			$this->domains = array();
		}
		return $this->domains;
	}
	
	/**
	 * Sets the domains
	 * @var string
	 */
	function setDomains($arg0) {
		if (is_array($arg0)) {
			$this->domains = $arg0;
		} else if (is_string($arg0)) {
			if (strpos($arg0, ",") !== false) {	
				$this->domains = explode(',', $arg0);
			} else {
				$this->domains = explode('\n', $arg0);
			}
			array_walk($this->domains, function(&$value) { return trim($value); });
			foreach ($this->domains as $key => $value) {
				if (trim($value) == '') {
					unset($this->domains[$key]);
				}
			}
			$this->addModifiedColumn('domains');
		}
		return $this;
	}
	
	/**
	 * Returns the is_gi_default
	 * @return boolean
	 */
	function getIsGiDefault() {
		if (is_null($this->is_gi_default)) {
			$this->is_gi_default = 0;
		}
		return $this->is_gi_default;
	}
	
	/**
	 * Sets the is_gi_default
	 * @var boolean
	 */
	function setIsGiDefault($arg0) {
		$this->is_gi_default = (int)$arg0;
		$this->addModifiedColumn('is_gi_default');
		return $this;
	}	
	
	/**
	 * Returns the email_count
	 * @return integer
	 */
	function getEmailCount() {
		if (is_null($this->email_count)) {
			$this->email_count = 0;
		}
		return $this->email_count;
	}
	
	/**
	 * Sets the email_count
	 * @var integer
	 */
	function setEmailCount($arg0) {
		$this->email_count = (int)$arg0;
		$this->addModifiedColumn('email_count');
		return $this;
	}
	
	/**
	 * Creates indexes for this collection
	 * @return boolean
	 */
	static function createIndexes() {
		$exception = null;
		$indexes = array();
		$indexes[] = array('idx' => array('name' => 1), 'options' => array('background' => true));
		$indexes[] = array('idx' => array('domains' => 1), 'options' => array('background' => true));
		foreach ($indexes as $index) {
			try {
				$collection = new self();
				$collection->getCollection()->createIndex($index['idx'], $index['options']);
			} catch (\Exception $e) {
				$exception = $e;
			}
		}
		
		if (!is_null($exception)) { throw $exception; }
	}
}