<?php
use Mojavi\Action\BasicConsoleAction;
use Mojavi\Util\StringTools;
use Mojavi\Request\Request;
use Mojavi\View\View;
// +---------------------------------------------------------------------------+
// | This file is part of the ISS package.                                     |
// | Copyright (c) 2006, 2007 Mark Hobson.                                     |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.redfiveconsulting.                      |
// +---------------------------------------------------------------------------+

class IpTestAction extends BasicConsoleAction
{
	/**
	 * Perform any execution code for this action
	 * @return integer (View::SUCCESS, View::ERROR, View::NONE)
	 */
	public function execute ()
	{
		try {
			/* @var $ip_test_form \Smta\IpTest */
			$ip_test_form = new \Smta\IpTest();
			$ip_test_form->populate($_REQUEST);
			$ip_test_form->testIps();
			
			$ip_test_form->outputIpResults();
		} catch (\Exception $e) {
			echo \Mojavi\Util\StringTools::consoleColor($e->getMessage(), \Mojavi\Util\StringTools::CONSOLE_COLOR_RED) . "\n";
		}
		
		return View::NONE;
	}

	/**
     * Returns the default view.  This view is used if the validation fails or if the method used in the form doesn't
     * match the list in getRequestMethods()
     * @return integer
     */
    public function getDefaultView ()
    {
    	return View::NONE;
    }

    /**
     * Sets the list of approved form methods that this action can service.
     * @return int 	-	Request::GET - Indicates that this action serves only GET requests, or...
     *             	- 	Request::POST - Indicates that this action serves only POST requests, or...
     *			- 	Request::NONE - Indicates that this action serves no requests, or...
     *			-	Request::POST | Request::GET  - Indicates that this action serves GET and POST requests
     */
    public function getRequestMethods ()
    {
        return Request::GET;
    }

    /**
     * Specifies whether the user must be authenticated (logged in) to use this action
     * @return boolean
     */
    public function isSecure()
	{
    	return false;
	}

	function isConsole() {
		return true;
	}
}

?>