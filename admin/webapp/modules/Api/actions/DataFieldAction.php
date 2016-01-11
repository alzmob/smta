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
class DataFieldAction extends BasicRestAction
{
	const DEBUG = MO_DEBUG;

	/**
	 * Returns the form to use for this rest request
	 * @return Form
	 */

	public function getInputForm() {
		return new \Smta\DataField();
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
	 * Executes a POST request
	 * @return \Mojavi\Form\BasicAjaxForm
	 */
	function executePost($input_form) {
		if (\MongoId::isValid($input_form->getId())) {
			return parent::executePut($input_form);
		}
		return parent::executePost($input_form);
	}
}

?>