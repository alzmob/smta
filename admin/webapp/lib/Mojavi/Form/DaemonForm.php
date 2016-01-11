<?php

/**
 * Description of Daemon
 */
namespace Mojavi\Form;

use \Smta\Daemon as DaemonDocument;

class DaemonForm extends MojaviForm {

	protected $jobsStarted = 0;
	protected $primaryThreads = array();
	protected $max_seconds_before_kill = 1800;
	protected $seconds_between_job_check = 2;
	protected $default_end_job_wait = 10;
	protected $currentJobs = array();
	protected $signalQueue = array();
	protected $pid;
	protected $daemon_class;
	protected $max_threads = 0;

	/**
	 * Constructs a new daemon based on the daemon class
	 * @return boolean
	 */
	public function __construct($daemon_class) {
		//check if daemon_class is instanceof daemon else throw exception
		$this->daemon_class = $daemon_class;
		$this->max_threads = $daemon_class->getThreads();
		$this->pid = getmypid();
	}

	/**
	 * Validates this daemon (used in children)
	 * @return boolean
	 */
	public function validate() {
		return true;
	}

	/**
	 * Resets this daemon (used in children)
	 * @return boolean
	 */
	public function reset() {
		return true;
	}

	/**
	 * Logs a message to the stdout
	 * @return void
	 */
	protected function log($msg, $identifier_array = array()) {
		$full_msg = '';
		$initial_identifier_array = array($this->daemon_class->getClassName(), date('Y-m-d H:i:s'));
		$identifier_array = array_merge($initial_identifier_array, $identifier_array);

		foreach($identifier_array AS $identifier_string) {
			$full_msg .= '[' . $identifier_string . '] ';
		}
		if (is_array($msg) || is_object($msg)) {
			$full_msg .= print_r($msg, true);
		} else {
			$full_msg .= $msg;
		}
		echo $full_msg . PHP_EOL;
	}

	/**
	 * Helper function to start this daemon
	 * @return boolean
	 */
	public function start() {
		//trying to start daemon, make sure it isn't already started
		$this->log('*****************************', array($this->pid));
		$this->log('Attempting to start new daemon for ' . $this->daemon_class->getType(), array($this->pid));
		$ret_val = $this->daemon_class->findAndModify(
			array('type' => $this->daemon_class->getType(), 'run_status' => DaemonDocument::DAEMON_RUN_STATUS_INACTIVE),
			array('$set' => array('run_status' => DaemonDocument::DAEMON_RUN_STATUS_ACTIVE)),
			null,
			array('new' => true)
		);
		if($ret_val instanceof DaemonDocument) {
			$pid_control = pcntl_fork();
			$this->pid = getmypid();
			if ($pid_control === -1) {
				$this->log('Daemon UNSUCCESSFULLY started', array($this->pid));
				$this->log('Launch fork error', array($this->pid));
				return false;
			} elseif ($pid_control > 0) {
				//Parent process
				$this->daemon_class->setPid($pid_control);
				$this->daemon_class->setStartTime(new \MongoDate());
				$this->daemon_class->update();
				$this->log('Daemon SUCCESSFULLY started with PID ' . $pid_control, array($this->pid));
				return true;
			} else {
				//Child process
				pcntl_signal(SIGTERM, array($this, 'killSignalHandler'));
				pcntl_signal(SIGCHLD, array($this, 'childSignalHandler'));
				pcntl_signal(SIGHUP, array($this, 'childSignalHandler'));
				$this->run();
			}
		} else {
			if ($this->daemon_class->getPid() !== null && $this->daemon_class->getPid() != '') {
				$out = shell_exec('ps ' . $this->daemon_class->getPid() . ' | wc -l');
				if($out >= 2) {
					$this->log('Daemon ' . $this->daemon_class->getType() . ' already running...', array($this->pid));
				} else {
					$this->log('Daemon ' . $this->daemon_class->getType() . ' crashed, resetting...', array($this->pid));
					$this->daemon_class->setPid(null);
					$this->daemon_class->setRunStatus(DaemonDocument::DAEMON_RUN_STATUS_INACTIVE);
					$this->daemon_class->update();
					sleep(1);
					$this->start();
				}
			} else {
				$this->log('Daemon ' . $this->daemon_class->getType() . ' crashed with null pid, resetting...', array($this->pid));
				$this->daemon_class->setPid(null);
				$this->daemon_class->setRunStatus(DaemonDocument::DAEMON_RUN_STATUS_INACTIVE);
				$this->daemon_class->update();
				sleep(1);
				$this->start();
			}
			return false;
		}
	}

	/**
	 * Helper function to stop this daemon
	 * @return boolean
	 */
	public function stop() {
		$this->log('*****************************', array($this->pid));
		$this->log('Attempting to stop ' . $this->daemon_class->getName(), array($this->pid));
		if (intval($this->daemon_class->getPid()) > 0) {
			$this->log('Stopping (' . $this->daemon_class->getPid() . ')...', array($this->pid));
			exec('kill -s ' . SIGTERM . ' ' . $this->daemon_class->getPid());

			$going = $this->status(false);
			$wait = $this->max_seconds_before_kill;

			while ($wait > 0 && $going) {
				sleep(1);
				$going = $this->status(false);
				$wait--;
				if ($wait % 60 == 0) {
					$this->log('Waited ' . ($this->max_seconds_before_kill - $wait). ' seconds...', array($this->pid));
				}
			}

			if ($going) {
				$this->log('Process is not stopping, issuing kill command', array($this->pid));
				exec('kill -s ' . SIGKILL . ' ' . $this->daemon_class->getPid());
			}

			$this->daemon_class->setPid(null);
			$this->daemon_class->setRunStatus(DaemonDocument::DAEMON_RUN_STATUS_INACTIVE);
			$this->daemon_class->update();

			return true;
		} else {
			$this->log('Was not running', array($this->pid));
			return false;
		}
	}

	/**
	 * Helper function to stop and start this daemon
	 * @return void
	 */
	public function restart() {
		$this->stop();
		$this->start();
	}

	/**
	 * Checks if this daemon is already running or not
	 * @return boolean
	 */
	public function status($verbose = true) {
		if (intval($this->daemon_class->getPid()) > 0) {
			$out = shell_exec('ps ' . $this->daemon_class->getPid() . ' | wc -l');
			if($out >= 2) {
				if ($verbose) {
					$this->log('Running as ' . $this->daemon_class->getPid(), array($this->pid));
				}
				return true;
			}
		}
		if ($verbose) {
			$this->log('Not running', array($this->pid));
		}
		return false;
	}

	/**
	 * Main function that runs this daemon and checks for new threads
	 * @return boolean
	 */
	public function run() {
		while (1) {
			while (count($this->currentJobs) >= $this->max_threads) {
				sleep($this->seconds_between_job_check);
			}
			if (count($this->primaryThreads) == 0) {
				$launched = $this->launchJob(true);
			} else {
				$launched = $this->launchJob();
			}
			
			if($launched === false) {
				//means one of the children didn't fork appropriately
				//not sure if that means we should kill the parent or not, for now we'll just do nothing
				//return false;
			}
		}
	}

	/**
	 * Starts a new job and handles forking
	 * @return boolean
	 */
	protected function launchJob($is_primary_thread = false) {
		$pid_control = pcntl_fork();
		$this->pid = getmypid();
		if ($pid_control === -1) {
			$this->log('Job fork error', array($this->pid));
			return false;
		} elseif ($pid_control > 0) {
			//Parent process
			$this->currentJobs[$pid_control] = 1;
			if (count($this->primaryThreads) == 0) {
				$this->primaryThreads[$pid_control] = 1;
			}
			if (isset($this->signalQueue[$pid_control])) {
				$this->childSignalHandler(SIGCHLD, $pid_control, $this->signalQueue[$pid_control]);
				unset($this->signalQueue[$pid_control]);
			}
			// Update the last spawn time
			$this->daemon_class->findAndModify(
				array('type' => $this->daemon_class->getType(), 'run_status' => DaemonDocument::DAEMON_RUN_STATUS_ACTIVE),
				array('$set' => array('last_run' => new \MongoDate())),
				null,
				array('new' => true)
			);
		} else {
			//Child process
			$daemon_class_name = $this->daemon_class->getClassName();
			$daemon = new $daemon_class_name();
			if ($is_primary_thread) { $daemon->setPrimaryThread(true); }
			$workDone = $daemon->runOne();
			if ($workDone === true) {
				//sleep(1);
			} elseif(is_int($workDone)) {
				sleep($workDone);
			} else {
				sleep($this->default_end_job_wait);
			}
			exit;
		}
		return true;
	}

	/**
	 * Handler to capture signals for the child processes
	 * @param integer $signo
	 * @param integer $pid
	 * @param integer $status
	 */
	public function childSignalHandler($signo, $pid = null, $status = null) {
		if (!$pid) {
			$pid = pcntl_waitpid(-1, $status, WNOHANG);
		}

		//Make sure we get all of the exited children
		while ($pid > 0) {
			if ($pid && isset($this->currentJobs[$pid])) {
				$exitCode = pcntl_wexitstatus($status);
				if ($exitCode != 0) {
					$this->log($pid . ' exited with status ' . $exitCode, array($pid));
				}
				unset($this->currentJobs[$pid]);
				if (isset($this->primaryThreads[$pid])) {
					unset($this->primaryThreads[$pid]);	
				}
			} elseif ($pid) {
				$this->signalQueue[$pid] = $status;
			}
			$pid = pcntl_waitpid(-1, $status, WNOHANG);
		}
		return true;
	}

	/**
	 * Handler to capture kill signals
	 * @param integer $signo
	 */
	public function killSignalHandler($signo) {
		$this->max_threads = 0;
		foreach (array_keys($this->currentJobs) as $pid) {
			exec('kill -s ' . SIGTERM . ' ' . $pid);
			$this->log('Waiting for children to exit (' . count($this->currentJobs) . ' remain)', array($this->pid));
			pcntl_waitpid($pid, $status);
			unset($this->currentJobs[$pid]);
			if (isset($this->primaryThreads[$pid])) { unset($this->primaryThreads[$pid]); }
		}
		$this->log('Daemon stopped', array($this->pid));
		exit;
	}
}
