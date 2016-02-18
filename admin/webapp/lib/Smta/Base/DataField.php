<?php
namespace Smta\Base;
use Mojavi\Form\MongoForm;
/**
 * DataField stores how a specific column should be formatted for input or output
 * @author Mark Hobson
 */
class DataField extends MongoForm {
	
	protected $name;
	protected $description;
	protected $key;
	protected $field_name;
	protected $custom_code;
	protected $tags;
	protected $is_system_field;
	protected $request_fields;
	protected $is_common_field;
	
	// +------------------------------------------------------------------------+
	// | CONSTRUCTOR															|
	// +------------------------------------------------------------------------+
	/**
	 * Constructs a new object
	 * @return DataField
	 */
	function __construct() {
		$this->setCollectionName('data_field');
		$this->setDbName('default');
	}
	
	/**
	 * Returns the is_common_field
	 * @return boolean
	 */
	function getIsCommonField() {
		if (is_null($this->is_common_field)) {
			$this->is_common_field = false;
		}
		return $this->is_common_field;
	}
	
	/**
	 * Sets the is_common_field
	 * @var boolean
	 */
	function setIsCommonField($arg0) {
		$this->is_common_field = (boolean)$arg0;
		$this->addModifiedColumn('is_common_field');
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
	 * Returns the field_name
	 * @return string
	 */
	function getFieldName() {
		if (is_null($this->field_name)) {
			$this->field_name = "";
		}
		return $this->field_name;
	}
	
	/**
	 * Sets the field_name
	 * @var string
	 */
	function setFieldName($arg0) {
		$this->field_name = $arg0;
		$this->addModifiedColumn('field_name');
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
	 * Returns the key
	 * @return string
	 */
	function getKey() {
		if (is_null($this->key)) {
			$this->key = "";
		}
		return $this->key;
	}
	
	/**
	 * Sets the key
	 * @var string
	 */
	function setKey($arg0) {
		$this->key = strtoupper(trim($arg0));
		$this->key = preg_replace("/[^a-z0-9A-Z]+/", "", $this->key);
		$this->key = "[" . $this->key . "]";
		$this->addModifiedColumn('key');
		return $this;
	}
	
	/**
	 * Returns the custom_code
	 * @return string
	 */
	function getCustomCode() {
		if (is_null($this->custom_code)) {
			$this->custom_code = "";
		}
		return $this->custom_code;
	}
	
	/**
	 * Sets the custom_code
	 * @var string
	 */
	function setCustomCode($arg0) {
		$this->custom_code = $arg0;
		$this->addModifiedColumn('custom_code');
		return $this;
	}
	
	/**
	 * Returns the tags
	 * @return array
	 */
	function getTags() {
		if (is_null($this->tags)) {
			$this->tags = array();
		}
		return $this->tags;
	}
	
	/**
	 * Sets the tags
	 * @var array
	 */
	function setTags($arg0) {
		if (is_array($arg0)) {
			$this->tags = $arg0;
			array_walk($this->tags, function(&$value) { $value = trim($value); });
			$this->addModifiedColumn('tags');
		} else if (is_string($arg0)) {
			if (strpos($arg0, ',') !== false) {
				$this->tags = explode(',', $arg0);
			} else if (strpos($arg0, '\n') !== false) {
				$this->tags = explode('\n', $arg0);
			} else {
				$this->tags = array($arg0);
			}
			array_walk($this->tags, function(&$value) { $value = trim($value); });
			$this->addModifiedColumn('tags');
		}
		return $this;
	}
	
	/**
	 * Returns the request_fields
	 * @return array
	 */
	function getRequestFields() {
		if (is_null($this->request_fields)) {
			$this->request_fields = array();
		}
		return $this->request_fields;
	}
	
	/**
	 * Sets the request_fields
	 * @var array
	 */
	function setRequestFields($arg0) {
		if (is_array($arg0)) {
			$this->request_fields = $arg0;
			array_walk($this->request_fields, function(&$value) { $value = trim($value); });
			$this->addModifiedColumn('request_fields');
		} else if (is_string($arg0)) {
			if (strpos($arg0, ',') !== false) {
				$this->request_fields = explode(',', $arg0);
			} else {
				$this->request_fields = explode('\n', $arg0);
			}
			array_walk($this->request_fields, function(&$value) { $value = trim($value); });
			$this->addModifiedColumn('request_fields');
		}
		return $this;
	}
	
	/**
	 * Returns the is_system_field
	 * @return boolean
	 */
	function getIsSystemField() {
		if (is_null($this->is_system_field)) {
			$this->is_system_field = false;
		}
		return $this->is_system_field;
	}
	
	/**
	 * Sets the is_system_field
	 * @var boolean
	 */
	function setIsSystemField($arg0) {
		$this->is_system_field = (boolean)$arg0;
		$this->addModifiedColumn('is_system_field');
		return $this;
	}
	
	/**
	 * Queries the data field by the key name
	 * @return DataField
	 */
	function queryByKey() {
		$criteria = array();
		$criteria['key'] = trim($this->getKey());
		$ret_val = parent::query($criteria, false);
		$this->populate($ret_val);
		return $this;
	}
	
	/**
	 * Creates indexes for this collection
	 * @return boolean
	 */
	static function createIndexes() {
		$exception = null;
		$indexes = array();
		$indexes[] = array('idx' => array('key' => 1), 'options' => array('background' => true));
		$indexes[] = array('idx' => array('request_fields' => 1), 'options' => array('background' => true));
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