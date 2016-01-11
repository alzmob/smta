<?php
namespace Smta;

/**
 * Setting contains methods to work with the setting table.
 *  
 * @author	Mark Hobson 
 * @since 03/13/2013 6:38 pm 
 */

class Setting extends Base\Setting 
{

	// +------------------------------------------------------------------------+
	// | CONSTANTS																|
	// +------------------------------------------------------------------------+
	const DEBUG = MO_DEBUG;
	
	// +------------------------------------------------------------------------+
	// | PRIVATE VARIABLES														|
	// +------------------------------------------------------------------------+
	private $setting_array;
	// +------------------------------------------------------------------------+
	// | PUBLIC METHODS															|
	// +------------------------------------------------------------------------+
	private static $_setting_cache = array();
	// +------------------------------------------------------------------------+
	// | RELATION METHODS														|
	// +------------------------------------------------------------------------+

	// +------------------------------------------------------------------------+
	// | HELPER METHODS															|
	// +------------------------------------------------------------------------+
	/**
	 * Returns the setting_array
	 * @return array
	 */
	function getSettingArray() {
		if (is_null($this->setting_array)) {
			$this->setting_array = array();
		}
		return $this->setting_array;
	}
	
	/**
	 * Sets the setting_array
	 * @var array
	 */
	function setSettingArray($arg0) {
		$this->setting_array = $arg0;
		return $this;
	}
	
	/**
	 * Queries by the name
	 * @return string
	 */
	function queryByName() {
		$criteria = array('name' => $this->getName());
		return parent::query($criteria, false);
	}
	
	/**
	 * Returns the setting value for a name
	 * @param string $setting_name
	 * @return string
	 */
	static function saveSetting($setting_name, $setting_value) {
		$setting = new \Rdm\Setting();
		$setting->setName(strtolower($setting_name));
		$setting->setValue($setting_value);
		$insert_id = $setting->update();
		return $insert_id;
	}
	
	/**
	 * Returns the setting value for a name
	 * @param string $setting_name
	 * @return string
	 */
	static function getSetting($setting_name, $default_value = '') {
		if (array_key_exists($setting_name, self::$_setting_cache)) {
			return self::$_setting_cache[$setting_name];
		} else {
			$setting = new \Smta\Setting();
			$setting->setName(strtolower($setting_name));
			if (($setting = $setting->queryByName()) !== false) {
				self::$_setting_cache[$setting_name] = $setting->getValue();
				return $setting->getValue();
			} else {
				return $default_value;	
			}
		}
	}
	
	/**
	 * Attempt to find the highest version based on the foldername in \Smta\Migration
	 * @return integer
	 */
	static function getNewestMigrationVersion() {
		$current_version = 0;
		$files = scandir(MO_WEBAPP_DIR . "/lib/Smta/Migration");
		foreach ($files as $file) {
			if (strpos($file, '.') === 0) { continue; }
			if (substr($file, 0, 3) == 'rev') {
				$version = intval(substr($file, 3));
				if ($version > $current_version) {
					$current_version = $version;
				}
			}
		}
		return $current_version;
	}
	
	/**
	 * Queries by the name
	 * @return string
	 */
	function insert() {
		return $this->update();
	}
	
	/**
	 * Queries by the name
	 * @return string
	 */
	function update($criteria_array = array(), $update_array = array(), $options_array = array('upsert' => true), $use_set_notation = false) {
		if (empty($this->getSettingArray())) {
			// If we don't have multiple values, then just save our setting
			return parent::updateMultiple(array('name' => $this->getName()), array('$set' => array('value' => $this->getValue())));	
		} else {
			// If we are saving multiple values, then do it one at a time
			foreach ($this->getSettingArray() as $key => $value) {
				$setting = new Setting();
				$setting->setName($key);
				$setting->setValue($value);
				$insert_id = $setting->update();
			}
			return $insert_id;
		}
	}
	
	/**
	 * Ensures the indexes
	 */
	function ensureIndex() {
		$this->getCollection()->ensureIndex(array('name' => 1), array('unique' => true));
	}
}
?>