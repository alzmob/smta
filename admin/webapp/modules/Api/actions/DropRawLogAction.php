<?php
use Mojavi\Action\BasicRestAction;
use Mojavi\View\View;
use Mojavi\Request\Request;
// +----------------------------------------------------------------------------+
// | This file is part of the Flux package.									  |
// |																			|
// | For the full copyright and license information, please view the LICENSE	|
// | file that was distributed with this source code.						   |
// +----------------------------------------------------------------------------+
class DropRawLogAction extends BasicRestAction
{
	const DEBUG = MO_DEBUG;

	/**
	 * Returns the form to use for this rest request
	 * @return Form
	 */

	public function getInputForm() {
		return new \Smta\Link\DropLog();
	}

	/**
	 * Perform any execution code for this action
	 * @return integer (View::SUCCESS, View::ERROR, View::NONE)
	 */
	public function execute ()
	{
		return parent::execute();
	}
	
	/**
	 * Executes a PUT request
	 * @return \Mojavi\Form\BasicAjaxForm
	 */
	function executeGet($input_form) {
		// Handle PUT Requests
		$ajax_form = new \Mojavi\Form\BasicAjaxForm();
		$drop = new \Smta\Link\DropLog();
		$drop->setId($input_form->getId());
		$drop->query();
		if (\MongoId::isValid($drop->getId())) {
			$drop->updateLog();
		}
		
		$ajax_form->setRecord($drop);
		return $ajax_form;
	}
}

?>