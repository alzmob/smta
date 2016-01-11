<?php
namespace Smta\Base;

use \Mojavi\Form\MongoForm;
/**
 * Ftp contains methods to work with the setting table.
 *  
 * @author	Mark Hobson 
 * @since 03/13/2013 6:38 pm 
 */
class Ftp extends MongoForm {

	const STATUS_PENDING_ACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	const STATUS_PENDING_INACTIVE = 3;
	const STATUS_PENDING_PASSWORD = 4;
	const STATUS_PROCESSING = 5;
	
	// +------------------------------------------------------------------------+
	// | PRIVATE VARIABLES														|
	// +------------------------------------------------------------------------+
	
	protected $is_master_ftp;
	protected $name;
	protected $username;
	protected $password;
	protected $home_folder;
	protected $port;
	protected $hostname;
	protected $file_mask;
	protected $file_filter;
	protected $file_prefix;
	protected $folder_name;
	protected $status;
	protected $is_processing;
	
	// +------------------------------------------------------------------------+
	// | CONSTRUCTOR															|
	// +------------------------------------------------------------------------+
	/**
	 * Constructs a new object
	 * @return AccountUser
	 */
	function __construct() {
	 	$this->setCollectionName('ftp');
	 	$this->setDbName('default');
	}
	
	/**
	 * Returns the is_master_ftp
	 * @return boolean
	 */
	function getIsMasterFtp() {
		if (is_null($this->is_master_ftp)) {
			$this->is_master_ftp = false;
		}
		return $this->is_master_ftp;
	}
	
	/**
	 * Sets the is_master_ftp
	 * @var boolean
	 */
	function setIsMasterFtp($arg0) {
		$this->is_master_ftp = $arg0;
		$this->addModifiedColumn('is_master_ftp');
		return $this;
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
		$this->addModifiedColumn('name');
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
		$this->addModifiedColumn('username');
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
		$this->addModifiedColumn('password');
		return $this;
	}
	
	/**
	 * Returns the home_folder
	 * @return string
	 */
	function getHomeFolder() {
		if (is_null($this->home_folder)) {
			$this->home_folder = "/home/" . $this->getUsername();
		}
		return $this->home_folder;
	}
	
	/**
	 * Sets the home_folder
	 * @var string
	 */
	function setHomeFolder($arg0) {
		$this->home_folder = $arg0;
		$this->addModifiedColumn('home_folder');
		return $this;
	}
	
	/**
	 * Returns the port
	 * @return integer
	 */
	function getPort() {
		if (is_null($this->port)) {
			$this->port = 21;
		}
		return $this->port;
	}
	
	/**
	 * Sets the port
	 * @var integer
	 */
	function setPort($arg0) {
		$this->port = (int)$arg0;
		$this->addModifiedColumn('port');
		return $this;
	}
	
	/**
	 * Returns the hostname
	 * @return string
	 */
	function getHostname() {
		if (is_null($this->hostname)) {
			$this->hostname = "";
		}
		return $this->hostname;
	}
	
	/**
	 * Sets the hostname
	 * @var string
	 */
	function setHostname($arg0) {
		$this->hostname = $arg0;
		$this->addModifiedColumn('hostname');
		return $this;
	}
	
	/**
	 * Returns the folder_name
	 * @return string
	 */
	function getFolderName() {
		if (is_null($this->folder_name)) {
			$this->folder_name = "";
		}
		return $this->folder_name;
	}
	
	/**
	 * Sets the folder_name
	 * @var string
	 */
	function setFolderName($arg0) {
		$this->folder_name = $arg0;
		$this->addModifiedColumn('folder_name');
		return $this;
	}
	
	/**
	 * Returns the file_mask
	 * @return string
	 */
	function getFileMask() {
		if (is_null($this->file_mask)) {
			$this->file_mask = "002";
		}
		return $this->file_mask;
	}
	
	/**
	 * Sets the file_mask
	 * @var string
	 */
	function setFileMask($arg0) {
		$this->file_mask = $arg0;
		$this->addModifiedColumn('file_mask');
		return $this;
	}
	
	/**
	 * Returns the file_filter
	 * @return string
	 */
	function getFileFilter() {
		if (is_null($this->file_filter)) {
			$this->file_filter = "";
		}
		return $this->file_filter;
	}
	
	/**
	 * Sets the file_filter
	 * @var string
	 */
	function setFileFilter($arg0) {
		$this->file_filter = $arg0;
		$this->addModifiedColumn('file_filter');
		return $this;
	}
	
	/**
	 * Returns the file_prefix
	 * @return string
	 */
	function getFilePrefix() {
		if (is_null($this->file_prefix)) {
			$this->file_prefix = "";
		}
		return $this->file_prefix;
	}
	
	/**
	 * Sets the file_prefix
	 * @var string
	 */
	function setFilePrefix($arg0) {
		$this->file_prefix = $arg0;
		$this->addModifiedColumn('file_prefix');
		return $this;
	}
	
	/**
	 * Returns the status
	 * @return integer
	 */
	function getStatus() {
		if (is_null($this->status)) {
			$this->status = self::STATUS_PENDING_ACTIVE;
		}
		return $this->status;
	}
	
	/**
	 * Sets the status
	 * @var integer
	 */
	function setStatus($arg0) {
		$this->status = (int)$arg0;
		$this->addModifiedColumn('status');
		return $this;
	}
	
	/**
	 * Returns the is_processing
	 * @return boolean
	 */
	function getIsProcessing() {
		if (is_null($this->is_processing)) {
			$this->is_processing = false;
		}
		return $this->is_processing;
	}
	
	/**
	 * Sets the is_processing
	 * @var boolean
	 */
	function setIsProcessing($arg0) {
		$this->is_processing = (boolean)$arg0;
		$this->addModifiedColumn("is_processing");
		return $this;
	}
	
	/**
	 * Creates indexes for this collection
	 * @return boolean
	 */
	static function createIndexes() {
		$exception = null;
		$indexes = array();
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
	// --------------------------------------------------------------------------
 
	
	// +------------------------------------------------------------------------+
	// | VALIDATE METHOD														|
	// +------------------------------------------------------------------------+

	// +------------------------------------------------------------------------+
	// | HELPER METHODS															|
	// +------------------------------------------------------------------------+
}