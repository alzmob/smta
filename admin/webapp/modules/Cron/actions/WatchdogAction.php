<?php
use Mojavi\Action\BasicConsoleAction;
use Mojavi\Util\StringTools;
use Mojavi\Request\Request;
use Mojavi\View\View;

/**
* Verifies that daemons are running
* @author Mark Hobson
* @since 11/27/2007 7:21 pm
*/
class WatchdogAction extends BasicConsoleAction {

	const DEBUG = MO_DEBUG;

	/**
	 * Perform any execution code for this action
	 * @return integer (View::SUCCESS, View::ERROR, View::NONE)
	 */
	public function execute ()
	{
	    try {
    		\Mojavi\Util\StringTools::consoleWrite('Watchdog started', null, \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
    		
    		// Check that vsftpd is running
    		\Mojavi\Util\StringTools::consoleWrite(' - Vsftpd', 'Checking', \Mojavi\Util\StringTools::CONSOLE_COLOR_YELLOW);
    		$cmd = 'ps aux | grep "vsftpd" | grep -v "grep" | wc -l';
    		$out = intval(shell_exec($cmd));
    		if ($out == 0) {
    		    \Mojavi\Util\StringTools::consoleWrite(' - Vsftpd', 'Starting', \Mojavi\Util\StringTools::CONSOLE_COLOR_RED);
    		    shell_exec('/sbin/service vsftpd start');
    		    \Mojavi\Util\StringTools::consoleWrite(' - Vsftpd', 'Started', \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
    		} else {
    		    \Mojavi\Util\StringTools::consoleWrite(' - Vsftpd', 'Running', \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
    		}
    		
    		// Verify that the daemons are running
    		$daemon = new \Smta\Daemon();
    		$daemon->setIgnorePagination(true);
    		$daemons = $daemon->queryAll();
    		/* @var $daemon \Smta\Daemon */
    		foreach ($daemons as $daemon) {
    		    // Only start daemons that have threads > 0
    		    if ($daemon->getThreads() > 0) {
    		        \Mojavi\Util\StringTools::consoleWrite(' - ' . $daemon->getName(), 'Checking', \Mojavi\Util\StringTools::CONSOLE_COLOR_YELLOW);
    		        $daemon_form = new \Mojavi\Form\DaemonForm($daemon);
    		        $is_running = $daemon_form->status(false);
    		        if (!$is_running) {
    		            \Mojavi\Util\StringTools::consoleWrite(' - ' . $daemon->getName(), 'Starting', \Mojavi\Util\StringTools::CONSOLE_COLOR_RED);
    		            $log_file = MO_LOG_FOLDER . '/daemon_' . strtolower($daemon->getType()) . '.log';
    		            $cmd = 'nice -n19 php ' . MO_DOCROOT_DIR . '/cron.php -m Cron -a Daemon --type=' . $daemon->getType() . ' --method=start &>' . $log_file .' &';
    		            shell_exec($cmd);
    		            \Mojavi\Util\StringTools::consoleWrite(' - ' . $daemon->getName(), 'Started', \Mojavi\Util\StringTools::CONSOLE_COLOR_YELLOW, true);
    		        } else {
    		            \Mojavi\Util\StringTools::consoleWrite(' - ' . $daemon->getName(), 'Running', \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
    		        }
    		    }
    		}
    				
    		\Mojavi\Util\StringTools::consoleWrite('Done', null, \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
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
