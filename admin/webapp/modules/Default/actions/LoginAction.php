<?php
use Mojavi\Action\BasicAction;
use Mojavi\View\View;
use Mojavi\Error\Error;
// +----------------------------------------------------------------------------+
// | This file is part of the Flux package.									  |
// |																			|
// | For the full copyright and license information, please view the LICENSE	|
// | file that was distributed with this source code.						   |
// +----------------------------------------------------------------------------+
class LoginAction extends BasicAction
{

	// +-----------------------------------------------------------------------+
	// | METHODS															   |
	// +-----------------------------------------------------------------------+
	const DEBUG = MO_DEBUG;
	/**
	 * Execute any application/business logic for this action.
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
		/* @var $user \Smta\User */
		$user = new \Smta\User();
		$user->populate($_REQUEST);
		$this->getContext()->getRequest()->setAttribute('user', $user);
		
		// figure out where we want to go after we login
		if (isset($_REQUEST['forward'])) {
			if ((strpos(strtolower($_REQUEST['forward']), "login") === false) && (strpos(strtolower($_REQUEST['forward']), "ajax") === false)) {
				$redirect = $_REQUEST['forward'];
			} else {
				$redirect = '/index';
			}
		} else if (isset($_REQUEST['module']) && isset($_REQUEST['action'])) {
			if ((strpos(strtolower($_REQUEST['action']), "login") === false) && (strpos(strtolower($_REQUEST['action']), "ajax") === false)) {
				$redirect = '/' . $_REQUEST['module'] . '/' . $_REQUEST['action'] . '?' . http_build_query($_GET, null, '&');
			} else {
				$redirect = '/index';
			}
		}
		
		// Perform a token login if we have a token
		if (isset($_REQUEST['token'])) {
			try {
				$user->tryTokenLogin();
				if (\MongoId::isValid($user->getId())) {
					// Successful token authentication
					setcookie('__cookie', (string)$user->getId(), (time() + 259200), "/", false);
					$this->getContext()->getUser()->setUserDetails($user);
					$this->getContext()->getUser()->setAuthenticated(true);
		
					$this->getContext()->getController()->redirect($redirect);
					return View::NONE;
		
				}
			} catch (\Exception $e) {
				\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $e->getMessage());
			}
		}
		
		// Perform a cookie login if we have a cookie
		if (isset($_COOKIE['__cookie'])) {
			$user_id = $_COOKIE['__cookie'];
			$user->setId($user_id);
			$user->query();
						
			setcookie('__cookie', (string)$user->getId(), (time() + 259200), "/", false);
			$this->getContext()->getUser()->setUserDetails($user);
			$this->getContext()->getUser()->setAuthenticated(true);
			
			$this->getContext()->getController()->redirect($redirect);
			return View::NONE;
		}
		
		// Perform a normal login
		if ($this->getContext()->getRequest()->getMethod() == \Mojavi\Request\Request::GET) {
			return View::SUCCESS;
		} else {
			// Only login if we are not authenticated
			if (strlen($user->getUsername()) == 0 || strlen($user->getPassword()) == 0) {
				$this->getErrors()->addError("error", new Error("You must enter an email address and password to login"));
				return View::SUCCESS;
			} else {
				/* @var $user Smta\User */
				try {
					$user->tryLogin();
				
					if (!\MongoId::isValid($user->getId())) {
						throw new \Exception("Your login credentials could not be validated. Please try again.");
					} else if (!$user->getActive()) {
						throw new \Exception("Your account is not currently active. Please contact customer service to re-activate your account.");
					}
				} catch (\Exception $e) {
					$this->getErrors()->addError("error", new Error($e->getMessage()));
					\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $e->getMessage());
					\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . $e->getTraceAsString());
				}
			}
		
				
			if ($this->getErrors()->isEmpty()) {
				$this->getContext()->getUser()->setUserDetails($user);
				$this->getContext()->getUser()->setAuthenticated(true);
				setcookie('__cookie', (string)$user->getId(), (time() + 259200), "/", false);

				$this->getContext()->getController()->redirect($redirect);
				return View::NONE;
			} else {
				return View::SUCCESS;
			}
		}
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
		return View::SUCCESS;
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
