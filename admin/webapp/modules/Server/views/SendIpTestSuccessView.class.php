<?php

// +---------------------------------------------------------------------------+
// | This file is part of the ISS package.									 |
// | Copyright (c) 2006, 2007 Mark Hobson.									 |
// |																		   |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the	|
// | LICENSE file online at http://www.redfiveconsulting.					  |
// +---------------------------------------------------------------------------+

require_once(MO_MODULE_DIR . "/Feeder/views/FeederIndexView.class.php");

class SendIpTestSuccessView extends FeederIndexView
{
	// +-----------------------------------------------------------------------+
	// | METHODS															   |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @return void
	 *
	 * @author Mark Hobson (hobby@redfiveconsulting.com)
	 * @since  1.0.0
	 */
	public function execute ()
	{
		parent::execute();
		// set our template
		$this->setTemplate('ServerSuccess.php');

		// set the title
		$this->setTitle('Server Information');
		
		$this->setDecoratorTemplate(MO_TEMPLATE_DIR . "/theme/" . MO_THEME_FOLDER . "/ajax.shell.php");
	}
}

?>