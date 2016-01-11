<?php
namespace Mojavi\Form;

use Mojavi\Logging\LoggerManager;
/**
 * OrmForm contains methods to make forms behave more like an ORM with save, update, delete, etc functions
 * built into it.
 */
class OrmForm extends DateRangeForm {
	
	/**
	 * Checks if the given record exists in the database
	 * @return boolean
	 */
	function exists() {
		// For right now, just check that the id is > 0
		return \MongoId::isValid($this->getId());
	}
	
	/**
	 * Clears the cache for this instance, if it exists
	 * @return boolean
	 */
	function clearCache() {
		if (defined('MO_USE_APC') && MO_USE_APC == '1') {
			if (function_exists("apc_exists")) {
				if (apc_exists(get_class($this) . "_" . $this->getId()) && $this->getId() > 0) {
					// Clear out the cache
					apc_delete(get_class($this) . "_" . $this->getId());
				}
			} else {
				LoggerManager::error(__METHOD__ . " :: " . "APC functions are not installed!");
			}
		}
		return true;
	}
	
	/**
	 * Queries a single record from the database given a primary key
	 * @return Form
	 */
	function query() {
		if (defined('MO_USE_APC') && MO_USE_APC == '1') {
			if (function_exists("apc_exists")) {
				if (apc_exists(get_class($this) . "_" . $this->getId()) && $this->getId() > 0) {
					$fetched_successfully = false;
					$cached_result = apc_fetch(get_class($this) . "_" . $this->getId(), $fetched_successfully);
					if ($fetched_successfully) {
						$this->populate($cached_result);
						return $cached_result;
					}
				}
			}
		}
		// If we don't get a cached result, then query from the db
		$model = $this->getModel();
		if (is_object($model)) {
			$result = $model->performQuery($this);
			$this->populate($result);
			
			if (defined('MO_USE_APC') && MO_USE_APC == '1') {
				if ($this->getId() > 0) {
					if (function_exists("apc_store")) {
						apc_store(get_class($this) . "_" . $this->getId(), $result, 14400);
					}
				}
			}
			
			return $result;
		}
		
		return $this;
	}
	
	/**
	 * Queries all the records from the database with available pagination
	 * @return DatabaseResultResource
	 */
	function queryAll() {
		$model = $this->getModel();
		if (is_object($model)) {
			$resultset = $model->performQueryAll($this);
			return $resultset;
		}
		return array();
	}
	
	/**
	 * Queries all the records from the database with available pagination
	 * @return integer
	 */
	function countAll() {
		$model = $this->getModel();
		if (is_object($model)) {
			$ret_val = $model->performCountAll($this);
			return $ret_val;
		}
		return 0;
	}
	
	/**
	 * Deletes a single record from the database given a primary key
	 * @return integer
	 */
	function delete() {
		$this->clearCache();
		$model = $this->getModel();
		if (is_object($model)) {
			$rows_affected = $model->performDelete($this);
			return $rows_affected;
		}
		return false;
	}
	
	/**
	 * Inserts a single record from the database given a primary key
	 * @return integer
	 */
	function insert() {
		$model = $this->getModel();
		if (is_object($model)) {
			$insert_id = $model->performInsert($this);
			return $insert_id;
		}
		return false;
	}
	
	/**
	 * Inserts a single record from the database given a primary key
	 * @return integer
	 */
	function cleanup() {
		$model = $this->getModel();
		if (is_object($model)) {
			$insert_id = $model->performCleanup($this);
			return $insert_id;
		}
		return false;
	}
	
	/**
	 * Updates a single record from the database given a primary key
	 * @return integer
	 */
	function update() {
		$this->clearCache();
		$model = $this->getModel();
		if (is_object($model)) {
			$rows_affected = $model->performUpdate($this);
			return $rows_affected;
		}
		return false;
	}
	
	/**
	 * Returns the model to use for ORM features
	 * @return Model
	 */
	function getModel() {
		$class_name = get_class($this);
		$class_name = str_replace('_Form_', '_Model_', $class_name);
		return new $class_name();
	}
	
	/**
	 * Placeholder method when you use this form
	 * @param Form $arg0
	 * @return number
	 */
	private function performUpdate(Form $arg0) {
		return 0;
	}
	
	/**
	 * Placeholder method when you use this form
	 * @param Form $arg0
	 * @return number
	 */
	private function performInsert(Form $arg0) {
		return 0;
	}
	
	/**
	 * Placeholder method when you use this form
	 * @param Form $arg0
	 * @return number
	 */
	private function performQuery(Form $arg0) {
		return new OrmForm();
	}
	
	/**
	 * Placeholder method when you use this form
	 * @param Form $arg0
	 * @return number
	 */
	private function performQueryAll(Form $arg0) {
		return array();
	}
	
	/**
	 * Placeholder method when you use this form
	 * @param Form $arg0
	 * @return number
	 */
	private function performDelete(Form $arg0) {
		return 0;
	}
}
