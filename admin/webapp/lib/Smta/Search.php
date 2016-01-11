<?php
namespace Smta;

class Search extends Base\Search {
	
	/**
	 * Searches for multiple items and returns them
	 * @see \Mojavi\Form\OrmForm::queryAll()
	 */
	function queryAll(array $criteria = array(), $hydrate = true, $fields = array()) {		
		$results = array();
		
		/* @var $drop \Smta\Drop */
		$drop = new \Smta\Drop();
		$drop->setKeywords($this->getKeywords());
		$drops = $drop->queryAll();
		foreach ($drops as $drop) {
			/* @var $search \Smta\Search */
			$search = new \Smta\Search();
			$search->setName($drop->getName());
			$search->setDescription($drop->getDescription());
			$search->setUrl('/drop/drop?_id=' . $drop->getId());
			$search->setSearchType(\Smta\Search::SEARCH_TYPE_DROPS);
			$results[] = $search;
		}
		
		return $results;
	}
	
}