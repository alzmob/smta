<?php
use \Mojavi\Action\BasicAction;
use \Mojavi\View\View;
use \Mojavi\Request\Request;
/**
* ApiServerAction goes to a page allowing you to edit a new record to the account table.
* Stores a list of accounts in the system 
* @author Mark Hobson 
* @since 11/27/2007 7:21 pm 
*/
class DaemonFormAction extends BasicAction {

	const DEBUG = MO_DEBUG;
	
	/**
	 * Perform any execution code for this action
	 * @return integer (View::SUCCESS, View::ERROR, View::NONE)
	 */
	public function execute ()
	{
		$daemon = new \Smta\Daemon();
		$daemon->populate($_REQUEST);
		$daemon->query();
		
		$daemon_classes = array();
		if (file_exists(MO_WEBAPP_DIR . "/lib/Smta/Daemon")) {
			$files = scandir(MO_WEBAPP_DIR . "/lib/Smta/Daemon");
			foreach ($files as $file) {
				if (strpos($file, '.') === 0) { continue; }
				if (strpos($file, 'Daemon.php') === 0) { continue; }
				$daemon_classes[] = '\\Smta\\Daemon\\' . str_replace(".php", "", $file);
			}
		}
		
		$this->getContext()->getRequest()->setAttribute('daemon', $daemon);
		$this->getContext()->getRequest()->setAttribute('daemon_classes', $daemon_classes);
		return View::SUCCESS;
	}
	
	/**
	 * Returns the default view.  This view is used if the validation fails or if the method used in the form doesn't 
	 * match the list in getRequestMethods()
	 * @return integer
	 */
	public function getDefaultView ()
	{
		return View::SUCCESS;
	}
	
	/**
	 * Sets the list of approved form methods that this action can service.
	 * @return int 	-	Request::GET - Indicates that this action serves only GET requests, or...
	 *			 	- 	Request::POST - Indicates that this action serves only POST requests, or...
	 *			- 	Request::NONE - Indicates that this action serves no requests, or...
	 *			-	Request::POST | Request::GET  - Indicates that this action serves GET and POST requests
	 */
	public function getRequestMethods ()
	{
		return Request::GET | Request::POST;
	}
	
	/**
	 * Specifies whether the user must be authenticated (logged in) to use this action
	 * @return boolean
	 */
	public function isSecure()
	{
		return true;
	}
} 
?>