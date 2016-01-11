<?php
namespace Mojavi\Filter;

/**
 * BasicSecurityFilter checks security by calling the getCredential() method
 * of the action. Once the credential has been acquired, BasicSecurityFilter
 * verifies the user has the same credential by calling the hasCredential()
 * method of SecurityUser.
 */
class BasicSecurityFilter extends SecurityFilter
{

	// +-----------------------------------------------------------------------+
	// | METHODS															   |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute this filter.
	 *
	 * @param FilterChain A FilterChain instance.
	 *
	 * @return void
	 */
	public function execute ($filterChain)
	{
		// get the cool stuff
		$context	= $this->getContext();
		$controller = $context->getController();
		$request	= $context->getRequest();
		$user	   = $context->getUser();

		// get the current action instance
		$actionEntry	= $controller->getActionStack()->getLastEntry();
		$actionInstance = $actionEntry->getActionInstance();
		
		// get the credential required for this action
		$credential = $actionInstance->getCredential();
		// credentials can be anything you wish; a string, array, object, etc.
		// as long as you add the same exact data to the user as a credential,
		// it will use it and authorize the user as having the credential
		//
		// NOTE: the nice thing about the Action class is that getCredential()
		//	   is vague enough to describe any level of security and can be
		//	   used to retrieve such data and should never have to be altered
		if ($user->isAuthenticated())
		{

			// the user is authenticated
			if ($credential === null || $user->hasCredential($credential))
			{

				// the user has access, continue
				$filterChain->execute();

			} else
			{

				// the user doesn't have access, exit stage left
				$controller->forward(MO_SECURE_MODULE, MO_SECURE_ACTION);

			}

		} else
		{
			// the user is not authenticated
			$controller->forward(MO_LOGIN_MODULE, MO_LOGIN_ACTION);

		}

	}

}

