<?php
use Mojavi\Action\BasicAction;
use Mojavi\View\View;
use Mojavi\Request\Request;

use Mojavi\Util\Ajax;
use Mojavi\Logging\LoggerManager;
// +----------------------------------------------------------------------------+
// | This file is part of the Flux package.									  |
// |																			|
// | For the full copyright and license information, please view the LICENSE	|
// | file that was distributed with this source code.						   |
// +----------------------------------------------------------------------------+
class ApiAction extends BasicAction
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
		try {
			/* @var $ajax_form \Mojavi\Util\Ajax */
			$ajax_form = new Ajax();
			$ajax_form->setFunc($this->getContext()->getRequest()->getParameter('func', ''));
			$ajax_form->setTimeout(300);
			$url = $this->getContext()->getRequest()->getParameter('_api_url', MO_API_URL);
			/* @var $response \Zend\Http\Response */
			$response = $ajax_form->send($this->getContext()->getRequest(), $this->getContext()->getRequest()->getMethod(), $url);
			$this->getContext()->getRequest()->setAttribute('response', $response);
		} catch (Exception $e) {
			\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $e->getMessage());
			\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $e->getTraceAsString());
			$this->getErrors()->addError('error', $e->getMessage());
		}
		return View::SUCCESS;
	}
	
	/**
	 * Sets the list of approved form methods that this action can service.
	 * @return int	 -	Request::GET - Indicates that this action serves only GET requests, or...
	 *				 -	 Request::POST - Indicates that this action serves only POST requests, or...
	 *			-	 Request::NONE - Indicates that this action serves no requests, or...
	 *			-	Request::POST | Request::GET  - Indicates that this action serves GET and POST requests
	 */
	public function getRequestMethods ()
	{
		return Request::GET | Request::POST | Request::PUT | Request::DELETE;
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