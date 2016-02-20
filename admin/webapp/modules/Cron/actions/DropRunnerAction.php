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
class DropRunnerAction extends BasicConsoleAction {

	const DEBUG = MO_DEBUG;
	const EMAIL_KEY = 'EMAIL';

	/**
	 * Perform any execution code for this action
	 * @return integer (View::SUCCESS, View::ERROR, View::NONE)
	 */
	public function execute ()
	{
	    try {
	    	/* @var \Smta\Drop */
	    	$drop = new \Smta\Drop();
	    	$drop->populate($_REQUEST);
	    	$drop->query();
	    	if (\MongoId::isValid($drop->getId())) {
	    		\Mojavi\Util\StringTools::consoleWrite('Drop #' . $drop->getId() . ' started at ' . date('m/d/Y g:i.s a'), null, \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
	    		$drop->updatePercent(10);
	    		if (!file_exists(MO_WEBAPP_DIR . '/meta/drops/')) {
	    			mkdir(MO_WEBAPP_DIR . '/meta/drops/');
	    		}
	    		
	    		if (!file_exists($drop->getListFileLocation())) {
	    			throw new \Exception('Cannot find list file at ' . $drop->getListFileLocation());
	    		}
	    		
	    		if ($drop->getBodyType() == \Smta\Drop::BODY_TYPE_FILENAME && !file_exists($drop->getBodyFilename())) {
	    			throw new \Exception('Cannot find body file at ' . $drop->getBodyFilename());
	    		}
	    		
	    		$drop->updatePercent(20);
	    		
	    		if ($drop->getBodyType() == \Smta\Drop::BODY_TYPE_FILENAME && file_exists($drop->getBodyFilename())) {
	    			$body_contents = file_get_contents($drop->getBodyFilename());
	    		} else {
	    			$body_contents = $drop->getBody();
	    		}
	    		
	    		$keys = array();
	    		/* @var $mapping \Smta\Link\DropMapping */
	    		foreach ($drop->getMapping() as $key => $mapping) {
	    			// Strip all special characters from the key
	    			$keys[$key] = strtoupper(trim(preg_replace("/[^a-z0-9A-Z]+/", "", $mapping->getName())));
	    		}
	    		ksort($keys);
	    		
	    		$drop->updatePercent(30);
	    		
	    		$from_prefix = 'info';
	    		$from_domain = $drop->getFromDomain();
	    		if (strpos($from_domain,'@') !== false) {
	    			$from_prefix = substr($from_domain, 0, strpos($from_domain, '@'));
	    			$from_domain = substr($from_domain, strpos($from_domain, '@') + 1);
	    		}
	    		
	    		if (($fh = fopen($drop->getListFileLocation(), 'r')) !== false) {
	    			$counter = 0;
	    			while (($line = fgetcsv($fh, 4096, $drop->getDelimiterCharacter(true))) !== false) {
	    				try {
		    				if (count($keys) != count($line)) {
		    					throw new \Exception('Keys don\'t match line count (' . count($keys) . ' <> ' . count($line) . ')');
		    				}
		    				
		    				$line_array = array_combine($keys, $line);
		    				if (isset($line_array[self::EMAIL_KEY])) {
			    				$tmp_body_contents = 'XACK OFF' . PHP_EOL;
		                        $tmp_body_contents .= 'EHLO ' . trim($from_domain) . PHP_EOL;
		                        $tmp_body_contents .= 'XMRG FROM: ' . $from_prefix . '@' . trim($from_domain) . ' VERP' . PHP_EOL;
		                        foreach ($line_array as $key => $value) {
		                        	$tmp_body_contents .= 'XDFN ' . (strtoupper($key) . '="' . trim($value) . '"') . PHP_EOL;
		                        }
		                        $tmp_body_contents .= 'XDFN FROM="' . trim($from_domain) . '"' . PHP_EOL;
		                        $tmp_body_contents .= 'XDFN *parts=1 *jobid="' . $drop->getId() . '"' . PHP_EOL;
		                        $tmp_body_contents .= 'RCPT TO: <' . trim($line_array[self::EMAIL_KEY]) . '>' . PHP_EOL;
								$tmp_body_contents .= 'XPRT 1 LAST' . PHP_EOL;
		                        $tmp_body_contents .= $body_contents;
		                        $tmp_body_contents .= PHP_EOL . '.' . PHP_EOL;
		                        
		                        $temp_filename = tempnam('/tmp', $drop->getId() . '_');
		                        if ($counter % 100 == 0) {
		                        	\Mojavi\Util\StringTools::consoleWrite('[ ' . $counter . ' ] Queued email ' . $line_array[self::EMAIL_KEY] . ' to ' . basename($temp_filename), null, \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
		                        }
		                        file_put_contents($temp_filename, $tmp_body_contents);
		                        
		                        chmod($temp_filename, 0777);
		                        rename($temp_filename, MO_WEBAPP_DIR . '/meta/drops/' . basename($temp_filename));
		    				} else {
		    					throw new \Exception('Email missing');
		    				}
	    				} catch (\Exception $e) {
	    					echo \Mojavi\Util\StringTools::consoleColor($e->getMessage(), \Mojavi\Util\StringTools::CONSOLE_COLOR_RED) . " on line #" . $counter . "\n";
	    				}
	    				
	    				$counter++;
	    			}
	    		} else {
	    			throw new \Exception('Cannot open list file at ' . $drop->getListFileLocation());
	    		}    		
	    		$drop->updatePercent(100);
	    		$drop->updateStopDrop();
	    		\Mojavi\Util\StringTools::consoleWrite('Done', null, \Mojavi\Util\StringTools::CONSOLE_COLOR_GREEN, true);
	    	} else {
	    		throw new \Exception('Invalid drop id passed');
	    	}
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
