<?php
use Mojavi\Action\BasicRestAction;
use Mojavi\View\View;
use Mojavi\Request\Request;
// +---------------------------------------------------------------------------+
// | This file is part of the ISS package.                                     |
// | Copyright (c) 2006, 2007 Mark Hobson.                                     |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.redfiveconsulting.                      |
// +---------------------------------------------------------------------------+

class IpTestStartAction extends BasicRestAction
{

	/**
	 * Returns the form to use for this rest request
	 * @return Form
	 */
	public function getInputForm() {
		return new \Smta\IpTest();
	}

	/**
	 * Perform any execution code for this action
	 * @return integer (View::SUCCESS, View::ERROR, View::NONE)
	 */
	public function execute ()
	{
		return parent::execute();
	}
	
	/**
     * Executes a POST request
     */
    function executePost($input_form) {
    	// Handle POST Requests
    	$ajax_form = new \Mojavi\Form\BasicAjaxForm();
    	    	
    	if (intval(trim(shell_exec('ps aux | grep "IpTestStart" | grep -v "grep" | wc -l'))) == 0) {
	    	/* @var $input_form DaoOffer_Form_IpTest */
	    	$cmd = 'sudo /home/smta/admin/webapp/meta/crons/test_ips.sh';
	    	$cmd .= ' --ip_range_array=' . implode(",", $input_form->getIpRangeArray());
	    	$cmd .= ' --seed_account=' . $input_form->getSeedAccount();
	    	$cmd .= ' --from_domain=' . $input_form->getFromDomain();
	    	$cmd .= ' --verbose=' . ($input_form->getVerbose() ? '1' : '0');
	    	$cmd .= ' --randomize_ips=' . ($input_form->getRandomizeIps() ? '1' : '0');
	    	$cmd .= ' --use_pipelining=' . ($input_form->getUsePipelining() ? '1' : '0');
	    	$cmd .= ' --disconnect_early=' . ($input_form->getDisconnectEarly() ? '1' : '0');
	    	$cmd .= ' > ' . $input_form->getLogFile() . ' &2>1 &';
    	
    		\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . "CMD: " . $cmd);
    	
    		shell_exec($cmd);
    	} else {
    		$this->getErrors()->addError('error', 'A test is already running on this strategy.  Wait for it to complete before starting another one');
    	}
    	
    	$ajax_form->setRecord($input_form);
   		return $ajax_form;
    }
}

?>