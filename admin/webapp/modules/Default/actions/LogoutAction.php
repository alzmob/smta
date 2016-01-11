<?php
use Mojavi\Action\BasicAction;
use Mojavi\View\View;
// +----------------------------------------------------------------------------+
// | This file is part of the Flux package.									  |
// |																			|
// | For the full copyright and license information, please view the LICENSE	|
// | file that was distributed with this source code.						   |
// +----------------------------------------------------------------------------+
class LogoutAction extends BasicAction
{

	// +-----------------------------------------------------------------------+
	// | METHODS															   |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute any application/business logic for this action.
	 *
	 * In a typical database-driven application, execute() handles application
	 * logic itself and then proceeds to create a model instance. Once the model
	 * instance is initialized it handles all business logic for the action.
	 *
	 * A model should represent an entity in your application. This could be a
	 * user account, a shopping cart, or even a something as simple as a
	 * single product.
	 *
	 * @return mixed - A string containing the view name associated with this
	 *				 action, or...
	 *			   - An array with three indices:
	 *				 0. The parent module of the view that will be executed.
	 *				 1. The parent action of the view that will be executed.
	 *				 2. The view that will be executed.
	 */
	public function execute ()
	{
		$this->getContext()->getUser()->setAuthenticated(false);
		$this->getContext()->getUser()->clearCredentials();
		$this->getContext()->getUser()->clearAttributes();
		setcookie('__cookie', "", 0, "/", false);
		$this->getContext()->getController()->redirect("/");
		return View::NONE;
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the default view to be executed when a given request is not
	 * served by this action.
	 *
	 * @return mixed - A string containing the view name associated with this
	 *				 action, or...
	 *			   - An array with three indices:
	 *				 0. The parent module of the view that will be executed.
	 *				 1. The parent action of the view that will be executed.
	 *				 2. The view that will be executed.
	 */
	public function getDefaultView ()
	{
		return View::NONE;
	}
	
	/**
	 * Indicates that this action requires security.
	 *
	 * @return bool true, if this action requires security, otherwise false.
	 */
	public function isSecure ()
	{
	
		return false;
	
	}

}

?>