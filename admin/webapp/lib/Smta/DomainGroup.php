<?php
namespace Smta;
/**
 * DomainGroup contains methods to work with the domain_group table.
 *  
 * @author	 
 * @since 02/19/2014 1:03 pm 
 */
class DomainGroup extends Base\DomainGroup
{

	// +------------------------------------------------------------------------+
	// | CONSTANTS																|
	// +------------------------------------------------------------------------+
	const DEBUG = MO_DEBUG;
	
	private static $domain_cache_array = array();
	private static $gi_domain_cache_array = array();
	
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
	 * Returns a list of expanded domains based on the suffixes and domains
	 * @return array
	 */
	function getExpandedDomains() {
		if ($this->getUseGlobalSuffixes()) {
			$suffixes = explode(",", \Smta\Setting::getSetting('DOMAIN_SUFFIXES', ''));
			array_walk($suffixes, function($value) { 
				$value = trim($value);
				if (strpos($value, '.') !== 0) {
					$value = ('.' . $value);
				}
			});
			$ret_val = array();
			foreach ($this->getDomains() as $domain) {
				if (strpos($domain, '.') === false) {
					foreach ($suffixes as $suffix) {
						$ret_val[] = $domain . $suffix;
					}					
				} else {
					$ret_val[] = $domain;
				}
			}
			return $ret_val;
		} else {
			return $this->getDomains();
		}
	}
	
	/**
	 * Gets the domain group from the domain
	 * Returns an array with the _id and name set
	 * @return array
	 */
	static function getGiDomainGroup() {
		$domain_group = new self();
		$gi_domain_groups = $domain_group->queryAll(array('is_gi_default' => 1));
		foreach ($gi_domain_groups as $gi_domain_group) {
			return $gi_domain_group;
		}
		return null;
	}
	
	/**
	 * Gets the domain group from the domain
	 * Returns an array with the _id and name set
	 * @return array
	 */
	static function getDomainGroupFromDomain($domain) {
	    if (empty(self::$domain_cache_array)) {
    	    /* @var $domain_cache \Smta\DomainGroup */
    	    $domain_cache = new \Smta\DomainGroup();
    	    $domain_cache->setIgnorePagination(true);
    	    $domain_groups = $domain_cache->queryAll();
    	    /* @var $domain_group \Rdm\DomainGroup */
    	    foreach ($domain_groups as $domain_group) {
    	        if ($domain_group->getIsGiDefault()) {
    	            self::$gi_domain_cache_array = array('_id' => $domain_group->getId(), 'name' => $domain_group->getName());
    	        } else {
	    	        self::$domain_cache_array[strtolower(trim($domain_group->getName()))] = array('_id' => $domain_group->getId(), 'name' => $domain_group->getName());
	    	        foreach ($domain_group->getExpandedDomains() as $expanded_domain) {
	    	            self::$domain_cache_array[strtolower(trim($expanded_domain))] = array('_id' => $domain_group->getId(), 'name' => $domain_group->getName());
	    	        }
    	        }
    	    }
	    }
	    if (trim($domain) != '' && array_key_exists(strtolower(trim($domain)), self::$domain_cache_array)) {
	        $ret_val = array('_id' => self::$domain_cache_array[strtolower(trim($domain))]['_id'], 'name' => self::$domain_cache_array[strtolower(trim($domain))]['name']);
	    } else {
	        $ret_val = array('_id' => self::$gi_domain_cache_array['_id'], 'name' => self::$gi_domain_cache_array['name']);
	    }
	    return $ret_val;
	}
	
	/**
	 * Queries for a data field by the name
	 * @return DomainGroup
	 */
	function queryByDomain($domain) {
		$criteria = array('domains' => $domain);
		$ret_val = parent::query($criteria, false);
		if ($ret_val == false) {
			// Return the GI domain group if we can't find an assigned domain group
			$criteria = array('is_gi_default' => 1);
			return parent::query($criteria, false);
		} else {
			return $ret_val;
		}
	}
	
	/**
	 * Queries for a data field by the name
	 * @return DomainGroup
	 */
	function queryByName() {
		$criteria = array('name' => $this->getName());
		return parent::query($criteria, false);
	}
	
	/**
	 * Updates the countries
	 * @return integer
	 */
	function update($criteria_array = array(), $update_array = array(), $options_array = array('upsert' => true), $use_set_notation = false) {
		if ($this->getIsGiDefault()) {
			// If this record is the default, then set all the other records to not default
			parent::updateMultiple(array(), array('$set' => array('is_gi_default' => false)));
		}
		return parent::update();
	}
}