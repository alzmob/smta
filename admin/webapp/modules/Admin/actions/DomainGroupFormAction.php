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
class DomainGroupFormAction extends BasicAction {

	const DEBUG = MO_DEBUG;
	
	/**
	 * Perform any execution code for this action
	 * @return integer (View::SUCCESS, View::ERROR, View::NONE)
	 */
	public function execute ()
	{
		$domain_group = new \Smta\DomainGroup();
		$domain_group->populate($_REQUEST);
		$domain_group->query();
		
		$this->getContext()->getRequest()->setAttribute('domain_group', $domain_group);
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