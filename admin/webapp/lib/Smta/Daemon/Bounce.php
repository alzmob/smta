<?php
namespace Smta\Daemon;

use Mojavi\Util\StringTools;

/**
 * Processes the bounce files in the bounce directory
 * @author Mark Hobson
 */
class Bounce extends BaseDaemon {
	
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
		
		$acct_folder = '/var/log/pmta/bounce/';
		$file = $this->getNextFile();
		if (!is_null($file)) {
			// Process the import
			$this->log('Processing bounce file ' . $file, array($this->pid));
			if (file_exists($acct_folder . DIRECTORY_SEPARATOR . $file)) {
				$awk_cols = array(
					'bounceCat' => '2',
					//'rcpt' => '3',
					//'timeLogged' => '7',
					'dsnAction' => '12',
					'jobId' => '4'
				);
				$cmd = 'head -n1 ' . $acct_folder . DIRECTORY_SEPARATOR . $file;
				$col_headers = explode(",", trim(shell_exec($cmd)));
				foreach ($col_headers as $key => $col_header) {
					if (trim($col_header) == 'bounceCat') {
						$awk_cols['bounceCat'] = ($key + 1);
					} else if (trim($col_header) == 'rcpt') {
						//$awk_cols['rcpt'] = ($key + 1);
					} else if (trim($col_header) == 'timeLogged') {
						//$awk_cols['timeLogged'] = ($key + 1);
					} else if (trim($col_header) == 'dsnAction') {
						$awk_cols['dsnAction'] = ($key + 1);
					} else if (trim($col_header) == 'jobId') {
						$awk_cols['jobId'] = ($key + 1);
					}
				}
					
				// Handle the bounce messages
				$cmd = 'cat ' . $acct_folder . DIRECTORY_SEPARATOR . $file . ' | grep "^b," | php -r \'if (($fh = fopen("php://stdin", "r")) !== false) { while (($line = fgetcsv($fh, 4096))) { echo implode("|", $line) . "\n"; } }\' | awk -F\| \'NR>1 {print $' . $awk_cols['bounceCat'] . '}\' | awk \'{a[$0]++}END{for(i in a)print i"|"a[i]}\' | awk -F\| \'{print $1}\'';
				$bounce_categories = explode("\n", trim(shell_exec($cmd)));
				foreach ($bounce_categories as $bounce_category) {
					if (trim($bounce_category) != '') {
						try {
							$this->log('Processing bounce category ' . $bounce_category . '...', array($this->pid));
							// Format will be message_class, recipient, message, mail_delivered, job_id
							$cmd = 'cat ' . $acct_folder . DIRECTORY_SEPARATOR . $file . ' | php -r \'if (($fh = fopen("php://stdin", "r")) !== false) { while (($line = fgetcsv($fh, 4096))) { echo implode("|", $line) . "\n"; } }\' | awk -F\| \'NR>1 {print $' . implode('"|"$', $awk_cols) . '}\' | awk \'{a[$0]++}END{for(i in a)print i"|"a[i]}\' | grep "' . trim($bounce_category) . '" | awk -F\| \'{print $3"\t"$4}\' ';
							$bounce_lines = explode("\n", trim(shell_exec($cmd)));
							foreach ($bounce_lines as $bounce_line) {
								if (strpos($bounce_line, "\t") !== false) {
									$bounce_parts = explode("\t", $bounce_line);
									$this->log('Processing bounce category ' . $bounce_category . "...Found " . $bounce_parts[1] . ' lines', array($this->pid));
									if ($bounce_category == 'hard-bounce' || $bounce_category == 'bad-mailbox' || $bounce_category == 'soft-bounce') {
										$drop = new \Smta\Drop();
										$drop->setId($bounce_parts[0]);
										$drop->query();
										if (\MongoId::isValid($drop->getId())) {
											$this->log('Updating drop with ' . $bounce_parts[1] . ' bounces', array($this->pid, $drop->getId()));
											$drop->update(array(), array('$inc' => array('report_stats.bounce_size' => (int)$bounce_parts[1])));
										} else {
											$this->log('Cannot find drop ' . $bounce_parts[0], array($this->pid));
										}
									}
								}
							}
						} catch (Exception $e) {
							$this->log('ERROR: ' . $e->getMessage(), array($this->pid));
						}
					}
				}
				
				
				// Now parse out the recipients and save them to the appropriate file
				$awk_cols = array(
						'bounceCat' => '2',
						'rcpt' => '3',
						'timeLogged' => '7',
						'dsnAction' => '12',
						'jobId' => '4'
				);
				$cmd = 'head -n1 ' . $acct_folder . DIRECTORY_SEPARATOR . $file;
				$col_headers = explode(",", trim(shell_exec($cmd)));
				foreach ($col_headers as $key => $col_header) {
					if (trim($col_header) == 'bounceCat') {
						$awk_cols['bounceCat'] = ($key + 1);
					} else if (trim($col_header) == 'rcpt') {
						$awk_cols['rcpt'] = ($key + 1);
					} else if (trim($col_header) == 'timeLogged') {
						$awk_cols['timeLogged'] = ($key + 1);
					} else if (trim($col_header) == 'dsnAction') {
						$awk_cols['dsnAction'] = ($key + 1);
					} else if (trim($col_header) == 'jobId') {
						$awk_cols['jobId'] = ($key + 1);
					}
				}
				// Handle the bounce messages
				$cmd = 'cat ' . $acct_folder . DIRECTORY_SEPARATOR . $file . ' | grep "^b," | php -r \'if (($fh = fopen("php://stdin", "r")) !== false) { while (($line = fgetcsv($fh, 4096))) { echo implode("|", $line) . "\n"; } }\' | awk -F\| \'NR>1 {print $' .  implode('"|"$', $awk_cols) . '}\'';
				$bounce_lines = explode("\n", trim(shell_exec($cmd)));
				foreach ($bounce_lines as $bounce_line) {
					$bounce_parts = explode("|", $bounce_line);
					if (!file_exists(MO_WEBAPP_DIR . "/meta/uploads/stats/" . $bounce_parts[4])) {
						mkdir(MO_WEBAPP_DIR . "/meta/uploads/stats/" . $bounce_parts[4], 0775);
					}
					if (($fh = fopen(MO_WEBAPP_DIR . "/meta/uploads/stats/" . $bounce_parts[4] . '/bounces.txt', 'a')) !== false) {
						fwrite($fh, implode(",", $bounce_parts) . "\n");
						fclose($fh);
					}
				}
				
				
				
				if (!rename($acct_folder . DIRECTORY_SEPARATOR . $file, $acct_folder . DIRECTORY_SEPARATOR . 'processed' . DIRECTORY_SEPARATOR . $file)) {
					$this->log('Error Deleting file ' . $file, array($this->pid));
				}
				
			} else {
				$this->log('Cannot open file ' . $file, array($this->pid));
			}
		}
		return false;
	}

	/**
	 * Finds the next import record to process and returns it
	 * @return string
	 */
	protected function getNextFile() {
		$acct_folder = '/var/log/pmta/bounce/';
		$files = scandir($acct_folder);
		foreach ($files as $file) {
			if (strpos($file, '.') === 0) { continue; }
			if (is_dir($acct_folder . $file)) { continue; }
			return $file;
		}
		return null;
	}
	
	/**
	 * Finds the number of pending records
	 * @return boolean
	 */
	protected function calculatePendingRecordCount() {
		$pending_records = count(scandir('/var/log/pmta/bounce/'));
		return parent::updatePendingRecordCount($pending_records);
	}
}