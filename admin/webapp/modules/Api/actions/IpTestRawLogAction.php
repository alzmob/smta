<?php
use Mojavi\Action\BasicRestAction;
use Mojavi\View\View;
use Mojavi\Request\Request;
// +---------------------------------------------------------------------------+
// | This file is part of the ISS package.                                     |
// | Copyright (c) 2006, 2007 Mark Hobson.                                     |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.redfiveconsulting.                      |
// +---------------------------------------------------------------------------+

class IpTestRawLogAction extends BasicRestAction
{

	/**
	 * Returns the form to use for this rest request
	 * @return Form
	 */
	public function getInputForm() {
		return new \Smta\IpTest();
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
     */
    function executeGet($input_form) {
    	// Handle POST Requests
    	$ajax_form = new \Mojavi\Form\BasicAjaxForm();
    	$input_form->getLogContents();
    	$ajax_form->setRecord($input_form);
   		return $ajax_form;
    }
}

?>