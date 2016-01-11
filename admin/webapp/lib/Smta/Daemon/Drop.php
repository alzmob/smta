<?php
namespace Smta\Daemon;

use Mojavi\Util\StringTools;

/**
 * Drop daemon will start and stop drops
 * @author Mark Hobson
 */
class Drop extends BaseDaemon {
	
	/**
	 * Process this daemon
	 * @return boolean
	 */
	public function action() {
		// Start our timer
		$start_time = microtime(true);
		
		// If we are the primary thread, then also update the pending record count
		if ($this->getPrimaryThread()) {
			$this->updateLastRunTime();
			$pending_records = $this->calculatePendingRecordCount(); 
		}
		
		/* @var $drop \Smta\Drop */
		$drop = $this->getNextDrop();
		if ($drop instanceof \Smta\Drop) {
			// Process the import
			if ($drop->getIsReadyToRun()) {
				$this->log(StringTools::consoleColor('Starting Drop #' . $drop->getId(), StringTools::CONSOLE_COLOR_GREEN), array($this->pid));
			} else {
				$this->log(StringTools::consoleColor('Stopping Drop #' . $drop->getId(), StringTools::CONSOLE_COLOR_RED), array($this->pid));
			}
			// Make sure that the import isn't already running.  If it is and this import is flagged as ready_to_import, then cancel the current one
			$cmd = 'ps auxf | grep -v "grep" | grep -v "\_" | grep "DropRunner" | grep "id=' . $drop->getId() . '$" | awk \'{print $2}\'';
			$existing_pids = explode("\n", shell_exec($cmd));
			if (count($existing_pids) > 0) {
				// A process already exists, so kill it
				foreach ($existing_pids as $existing_pid) {
					if (intval($existing_pid) > 0) {
						$this->log(StringTools::consoleColor('Killing existing process ' . $existing_pid, StringTools::CONSOLE_COLOR_YELLOW), array($this->pid));
						posix_kill(intval($existing_pid), SIGTERM);
					}
				}
			}
			
			// Make sure we only run up to 10 imports at a time
			$cmd = 'ps auxf | grep -v "grep" | grep -v "\_" | grep "DropRunner" | awk \'{print $2}\' | wc -l';
			$existing_imports = intval(shell_exec($cmd));
			if ($existing_imports < 10) {
				if ($drop->getIsReadyToRun()) {
					// Create the log file and update the import
					$log_file = MO_LOG_FOLDER . "/drop_runner.sh_" . $drop->getId() . ".log";
					$drop->update(
						array('_id' => $drop->getId()),
						array(
							'$set' => array(
								'log_filename' => $log_file,
								'is_running' => true,
								'is_ready_to_run' => false
							)
						)
					);
					$cmd = MO_WEBAPP_DIR . "/meta/crons/drop_runner.sh " . $drop->getId() . " --silent 2>&1";
					shell_exec($cmd);
				} else {						
					$drop->update(
						array('_id' => $drop->getId()),
						array('$set' => array(
								'is_running' => false,
								'is_ready_to_run' => false,
								'is_ready_to_stop' => false
							)
						)
					);
				}
			}
			
			$drop->update(array('_id' => $drop->getId()), array('$unset' => array('pid_import' => 1), '$set' => array('last_run_time' => new \MongoDate())), array());
			
			sleep(10);
			return true;
		} else {
			// If we are the primary thread, then also update the pending record count
			if ($this->getPrimaryThread()) {
				//$this->clearExpiredPids();
				$this->log('No more records found...(' . number_format(microtime(true) - $start_time, 3) . 's)', array($this->pid));
			}
			sleep(5);
		}
		return false;
	}
	
	/**
	 * Clears expired pids
	 * @return \Smta\Drop
	 */
	protected function clearExpiredPids() {
		// If there are no exports, then let's clean up some of the older ones
		$drop = new \Smta\Drop();
		$criteria = array(
				'pid_import' => array('$exists' => true),
				'created_time' => array('$gte' => new \MongoDate(strtotime(date('m/d/Y 00:00:00', strtotime('now')))))
		);
		
		$drops = $drop->queryAll($criteria);
		if (count($drops) > 0) {
			foreach ($drops as $drop) {
				if ($drop->getPidImport() != '') {
					$cmd = 'ps -p ' . $drop->getPidImport() . ' | grep -v "PID"';
					$cmd_response = trim(shell_exec($cmd));
					if ($cmd_response == '') {
						// if there isn't a process running, then clear the PID
						$drop->clearImportPid();
					}
				} else {
					$drop->clearImportPid();
				}
			}
		}
	}

	/**
	 * Finds the next import record to process and returns it
	 * @return \Rdm\ImportRecord
	 */
	protected function getNextDrop() {
		$drop = new \Smta\Drop();
		// Find active splits with no pid, set the pid, and return the split
		$criteria = array(
				'$or' => array(array('is_ready_to_run' => true), array('is_ready_to_stop' => true)),
				'pid_import' => array('$exists' => false)
		);
		$drop_item = $drop->findAndModify(
			$criteria,
			array('$set' => array(
				'pid_import' => $this->pid,
				'__pid_time_import' => new \MongoDate()
			)),
			null,
			array(
				'new' => true,
				'sort' => array('__pid_time_import' => 1)
			)
		);
		return $drop_item;
	}
	
	/**
	 * Finds the number of pending records
	 * @return boolean
	 */
	protected function calculatePendingRecordCount() {
		$drop = new \Smta\Drop();
		// Find active splits with no pid, set the pid, and return the split
		$criteria = array(
			'is_ready_to_run' => true,
			'pid_import' => array('$exists' => false)
		);
		$pending_records = $drop->count($criteria);
		return parent::updatePendingRecordCount($pending_records);
	}
}