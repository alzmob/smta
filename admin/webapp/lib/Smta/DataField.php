<?php
namespace Smta;

/**
 * DataField contains methods to work with the data_field table.
 *  
 * @author	 
 * @since 02/19/2014 1:03 pm 
 */
class DataField extends Base\DataField
{

	// +------------------------------------------------------------------------+
	// | CONSTANTS																|
	// +------------------------------------------------------------------------+
	const DEBUG = MO_DEBUG;
	
	// +------------------------------------------------------------------------+
	// | PRIVATE VARIABLES														|
	// +------------------------------------------------------------------------+

	// +------------------------------------------------------------------------+
	// | PUBLIC METHODS															|
	// +------------------------------------------------------------------------+

	// +------------------------------------------------------------------------+
	// | RELATION METHODS														|
	// +------------------------------------------------------------------------+

	// +------------------------------------------------------------------------+
	// | HELPER METHODS															|
	// +------------------------------------------------------------------------+
	
	/**
	 * Queries a list of the unique tag names
	 * @return array
	 */
	static function queryUniqueTagNames() {
		$ret_val = array();	
		$data_field = new \Smta\DataField();
		$results = $data_field->getCollection()->aggregate(array(array('$unwind' => '$tags'), array('$group' => array('_id' => array('tags' => '$tags'), 'tag_name' => array('$max' => '$tags')))), array('allowDiskUse' => true, 'maxTimeMS' => 1200000));
		if (isset($results['result'])) {
			foreach ($results['result'] as $result) {
				$ret_val[] = $result['tag_name'];
			}
			asort($ret_val);
			return $ret_val;
		} else {
			return $ret_val;
		}
	}
	
	/**
	 * Queries for a data field by the name
	 * @return DataField
	 */
	function queryByName() {
		$criteria = array('name' => $this->getName());
		return parent::query($criteria, false);
	}
	
	/**
	 * Queries for a data field by the name
	 * @return DataField
	 */
	function queryAll(array $criteria = array(), $hydrate = true, $fields = array()) {
		if (trim($this->getName()) != '') {
			$criteria['$or'] = array(
								array('name' => new \MongoRegex('/' . trim($this->getName()) . '/i')),
								array('tags' => new \MongoRegex('/' . trim($this->getName()) . '/i')),
								array('description' => new \MongoRegex('/' . trim($this->getName()) . '/i')),
								array('key' => new \MongoRegex('/' . trim($this->getName()) . '/i'))
							   );
		}
		return parent::queryAll($criteria, $hydrate, $fields);
	}
	
	/**
	 * Returns the first available request field to use for post strings
	 * @return string
	 */
	function getRequestField() {
		$request_fields = $this->getRequestFields();
		$first_request_field = array_shift($request_fields);
		return $first_request_field;
	}
	
	/**
	 * Returns the data field value
	 * @param $record \Rdm\Record
	 * @return string
	 */
	function getMappedValue($record) {
		if (is_array($record)) {
			$getters = explode(".", $this->getFieldName());
			$value = $record;
			foreach ($getters as $getter) {
				if (array_key_exists($getter, $value)) {
					$value = $value[$getter];
				} else {
					$value = null;
					break;
				}
			}
			$ret_val = $this->callMappingFunc($value, $record);
			if (is_array($ret_val)) {
				$ret_val = implode(",", $ret_val);
			} else if (is_object($ret_val) && $ret_val instanceof \MongoDate) {
				$ret_val = date('Y-m-d H:i:s', $ret_val->sec);
			} else if (is_object($ret_val) || is_array($ret_val)) {
				$ret_val = null;
			}
			/*
			if (is_null($ret_val) && \Smta\Setting::getSetting('NULL_VALUE_ACTION') == '1') {
				$ret_val = '';
			}
			*/
			return $ret_val;  
		} else if ($record instanceof \Rdm\Base\Record) {
			$getters = explode(".", $this->getFieldName());
			$value = $record;
			foreach ($getters as $getter) {
				$method_name = 'get' . ucfirst(\Mojavi\Util\StringTools::camelCase($getter));
				if (method_exists($value, $method_name)) {
					$value = $value->{$method_name}();
					if (!is_object($value)) {
						break;
					}
				} else {
					$value = null;
					break;
				}
			}
			$ret_val = $this->callMappingFunc($value, $record);
			if (is_array($ret_val)) {
				$ret_val = implode(",", $ret_val);
			} else if (is_object($ret_val) && $ret_val instanceof \MongoDate) {
				$ret_val = date('Y-m-d H:i:s', $ret_val->sec);
			} else if (is_object($ret_val) || is_array($ret_val)) {
				$ret_val = null;
			}
			/*
			if (is_null($ret_val) && \Smta\Setting::getSetting('NULL_VALUE_ACTION') == '1') {
				$ret_val = '';
			}
			*/
			return $ret_val;
		} else {
			\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . 'Function called without an array');
		}	
		return "";
	}
	
	/**
	 * Returns the data field value
	 * @param $record \Rdm\Record
	 * @return string
	 */
	function setMappedValue($record, $value) {
		$getters = explode(".", $this->getFieldName());
		$tmp_record = $record;
		foreach ($getters as $key => $getter) {
			if ($key == (count($getters) - 1)) {
				$method_name = 'set' . ucfirst(\Mojavi\Util\StringTools::camelCase($getter));
				if (method_exists($tmp_record, $method_name)) {
					$tmp_record->{$method_name}($value);
				}
			} else {
				$method_name = 'get' . ucfirst(\Mojavi\Util\StringTools::camelCase($getter));
				if (method_exists($tmp_record, $method_name)) {
					$tmp_record = $tmp_record->{$method_name}();
					if (!is_object($tmp_record)) {
						break;
					}
				}
			}
			
		}
		return $record;
	}
	
	/**
	 * Calls the custom mapping function
	 * @param $value string
	 * @param $record array
	 * @return string
	 */
	function callMappingFunc($value, $record) {
		$ret_val = '';
		try {
			if (trim($this->getCustomCode()) == '') { return $value; }
			// Define a default mapping function
			$mapping_func = function($value, $record) { return $value; };
			$errors = '';
			// Now overwrite the default mapping function
			@ob_start();
			$new_mapping_func = '<?php $mapping_func = function ($value, $record) {' . $this->getCustomCode() . '}; ?>';
			include "data://text/plain;base64,".base64_encode($new_mapping_func);
			//eval('$mapping_func = function ($value, $record) {' . $this->getCustomCode() . '};');
			if (ob_get_length() > 0) {
				$errors = ob_get_contents();
			}
			@ob_end_clean();
			if (trim($errors) != '') {
				throw new \Exception("Error evaluating mapping " . $this->getFieldName() . ": ". $errors);
			}
			// Finally call the mapping function and return the result
			$ret_val = $mapping_func($value, $record);
		} catch (\Exception $e) {
			\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $e->getMessage());
			$ret_val = $value;
		}
		$errors = null;
		$mapping_func = null;
		//$value = null;
		
		return $ret_val;
	}
}
?>