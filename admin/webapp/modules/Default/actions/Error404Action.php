<?php
use Mojavi\Action\BasicRestAction;
use Mojavi\Form\CommonForm;
// +---------------------------------------------------------------------------+
// | This file is part of the Mojavi package.                                  |
// | Copyright (c) 2003, 2004 Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.mojavi.org.                             |
// +---------------------------------------------------------------------------+
class Error404Action extends BasicRestAction
{

	/**
	 * Executes logic for this action
	 * @return void
	 */
	function execute() {
		$this->getErrors()->addError('error', '404: The page cannot be found');
		return parent::execute();
	}
	
	/**
	 * Returns the input form to use for this rest action
	 * @return AccountForm
	 */
	function getInputForm() {
		return new \Mojavi\Form\CommonForm();	
	}

}

?>