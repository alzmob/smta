<?php
namespace Smta\Base;

use \Mojavi\Form\MongoForm;
/**
 * DaoAccount_Form_BaseSetting contains methods to work with the setting table.
 *  
 * @author	Mark Hobson 
 * @since 03/13/2013 6:38 pm 
 */
class Setting extends MongoForm {

	// +------------------------------------------------------------------------+
	// | PRIVATE VARIABLES														|
	// +------------------------------------------------------------------------+

	protected $name;  
	protected $value;  

	// +------------------------------------------------------------------------+
	// | CONSTRUCTOR															|
	// +------------------------------------------------------------------------+
	/**
	 * Constructs a new object
	 * @return User
	 */
	function __construct() {
	 	$this->setCollectionName('setting');
	 	$this->setDbName('default');
	}

	// +------------------------------------------------------------------------+
	// | PUBLIC METHODS															|
	// +------------------------------------------------------------------------+

	// --------------------------------------------------------------------------
	/**
	 * Returns the name 
	 *  
	 * @return	string 
	 */
	public function getName()
	{
		if (is_null($this->name))
		{
			$this->name = "";
		}
		return $this->name;
	}

	// --------------------------------------------------------------------------

	/**
	 * Sets the name 
	 *  
	 * @param	string 
	 * @return	void
	 */
	public function setName($arg0)
	{
		$this->addModifiedColumn("name");
		$this->name = $arg0;
		return $this;
	}

	// --------------------------------------------------------------------------
	/**
	 * Returns the value 
	 *  
	 * @return	string 
	 */
	public function getValue()
	{
		if (is_null($this->value))
		{
			$this->value = "";
		}
		return $this->value;
	}

	// --------------------------------------------------------------------------

	/**
	 * Sets the value 
	 *  
	 * @param	string 
	 * @return	void
	 */
	public function setValue($arg0)
	{
		$this->addModifiedColumn("value");
		$this->value = $arg0;
		return $this;
	}
	
	/**
	 * Creates indexes for this collection
	 * @return boolean
	 */
	static function createIndexes() {
		$exception = null;
		$indexes = array();
		$indexes[] = array('idx' => array('name' => 1), 'options' => array('unique' => true, 'background' => true));
	
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
?>