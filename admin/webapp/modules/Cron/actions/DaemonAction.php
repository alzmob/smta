<?php
// +----------------------------------------------------------------------------+
// | This file is part of the Rdm package.                                      |
// |                                                                            |
// | For the full copyright and license information, please view the LICENSE    |
// | file that was distributed with this source code.                           |
// +----------------------------------------------------------------------------+
declare(ticks=1);

use Mojavi\Action\BasicConsoleAction;
use Mojavi\Util\StringTools;
use Mojavi\View\View;

class DaemonAction extends BasicConsoleAction
{

    // +-----------------------------------------------------------------------+
    // | METHODS                                                               |
    // +-----------------------------------------------------------------------+

    /**
     * Execute any application/business logic for this action.
     * @return mixed - A string containing the view name associated with this action
     */
    public function execute ()
    {
        try {
            $type = $this->getContext()->getRequest()->getParameter('type', '');
            $method = $this->getContext()->getRequest()->getParameter('method', '');
            
            if (trim($type) == '') {
                throw new \Exception("You must specify a type of daemon to run");
            }
            if (trim($method) == '') {
                throw new \Exception('Usage: daemon_sh [ start|stop|restart|runOne|runThree|status ]');
            }
            
            /* @var $daemon \Smta\Daemon */
            $daemon = new \Smta\Daemon();
            $ret_val = $daemon->query(array('type' => $type), false);
            if ($ret_val instanceof \Smta\Daemon) {
                $daemon_class_name = $daemon->getClassName();
                $threads = $daemon->getThreads();
                switch($method) {
                    case 'start':
                        $daemon_form = new \Mojavi\Form\DaemonForm($daemon);
                        $daemon_form->start();
                        break;
                    case 'stop':
                        $daemon_form = new \Mojavi\Form\DaemonForm($daemon);
                        $daemon_form->stop();
                        break;
                    case 'restart':
                        $daemon_form = new \Mojavi\Form\DaemonForm($daemon);
                        $daemon_form->restart();
                        break;
                    case 'runOne':
                        $single = new $daemon_class_name();
                        $single->runOne();
                        break;
                    case 'runThree':
                        $single = new $daemon_class_name();
                        $single->runOne();
                        $single->runOne();
                        $single->runOne();
                        break;
                    case 'status':
                        $daemon_form = new \Mojavi\Form\DaemonForm($daemon);
                        $daemon_form->status();
                        break;
                    default:
                        throw new \Exception('Usage: daemon_sh [ start|stop|restart|runOne|runThree|status ]');
                        break;
                }
            } else {
            	$valid_daemons = $daemon->queryAll();
            	$valid_types = array();
            	foreach ($valid_daemons as $valid_daemon) {
            		$valid_types[] = $valid_daemon->getType();
            	}
                throw new \Exception("No daemon of this type (" . $type . "). Valid types are [ " . implode("|", $valid_types) . " ]");
            }
        } catch (\Exception $e) {
            echo StringTools::consoleColor($e->getMessage(), StringTools::CONSOLE_COLOR_RED) . PHP_EOL;
        }
        return View::NONE;
    }
    
}