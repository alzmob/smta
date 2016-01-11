<?php
use Mojavi\Action\BasicAction;
use Mojavi\View\View;
use Mojavi\Request\Request;
// +----------------------------------------------------------------------------+
// | This file is part of the Flux package.									  |
// |																			|
// | For the full copyright and license information, please view the LICENSE	|
// | file that was distributed with this source code.						   |
// +----------------------------------------------------------------------------+
class DropMappingAction extends BasicAction
{

	// +-----------------------------------------------------------------------+
	// | METHODS															   |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute any application/business logic for this action.
	 *
	 * @return mixed - A string containing the view name associated with this action
	 */
	public function execute ()
	{
		/* @var $drop \Smta\Drop */
		$drop = new \Smta\Drop();
		$drop->populate($_REQUEST);
		$drop->query();
		
		$data_field = new \Smta\DataField();
		$data_field->setIgnorePagination(true);
		$data_fields = $data_field->queryAll();
		
		$this->getContext()->getRequest()->setAttribute('drop', $drop);
		$this->getContext()->getRequest()->setAttribute('data_fields', $data_fields);
		
		return View::SUCCESS;
	}
	
	/**
	 * Indicates that this action requires security.
	 *
	 * @return bool true, if this action requires security, otherwise false.
	 */
	public function isSecure ()
	{
	
		return true;
	
	}
}

?>