<?php
namespace Smta\Daemon;

use Mojavi\Util\StringTools;

/**
 * Processes the bounce files in the bounce directory
 * @author Mark Hobson
 */
class Delivered extends BaseDaemon {
	
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
		
		$acct_folder = '/var/log/pmta/delivered';
		$file = $this->getNextFile();
		if (!is_null($file)) {
			// Process the import
			$this->log('Processing delivered file ' . $file, array($this->pid));
			if (file_exists($acct_folder . DIRECTORY_SEPARATOR . $file) && !is_dir($acct_folder . DIRECTORY_SEPARATOR . $file)) {
				$awk_cols = array(
					//'bounceCat' => '2',
					//'rcpt' => '3',
					//'timeLogged' => '7',
					'dsnAction' => '12',
					'jobId' => '4'
				);
				$cmd = 'head -n1 ' . $acct_folder . DIRECTORY_SEPARATOR . $file;
				$col_headers = explode(",", trim(shell_exec($cmd)));
				foreach ($col_headers as $key => $col_header) {
					if (trim($col_header) == 'bounceCat') {
						//$awk_cols['bounceCat'] = ($key + 1);
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
					
				// Format will be message_class, recipient, message, mail_delivered, job_id
				$cmd = 'cat ' . $acct_folder . DIRECTORY_SEPARATOR . $file . ' | php -r \'if (($fh = fopen("php://stdin", "r")) !== false) { while (($line = fgetcsv($fh, 4096))) { echo implode("|", $line) . "\n"; } }\' | awk -F\| \'NR>1 {print $' . implode('"|"$', $awk_cols) . '}\' | awk \'{a[$0]++}END{for(i in a)print i"|"a[i]}\' | awk -F\| \'{print $2"\t"$3}\' ';
				$delivered_lines = explode("\n", trim(shell_exec($cmd)));
				foreach ($delivered_lines as $delivered_line) {
					if (strpos($delivered_line, "\t") !== false) {
						$delivered_parts = explode("\t", $delivered_line);
						$this->log('Processing delivered...Found ' . $delivered_parts[1] . ' lines', array($this->pid, $delivered_parts[0]));
						
						$drop = new \Smta\Drop();
						$drop->setId($delivered_parts[0]);
						$drop->query();
						if (\MongoId::isValid($drop->getId())) {
							$this->log('Updating drop with ' . $delivered_parts[1] . ' delivered', array($this->pid, $drop->getId()));
							$drop->update(array(), array('$inc' => array('report_stats.delivered_size' => (int)$delivered_parts[1])));
						} else {
							$this->log('Cannot find drop ' . $delivered_parts[0], array($this->pid));
						}
					}
				}
				
				// Now parse out the recipients and save them to the appropriate file
				$awk_cols = array(
						//'bounceCat' => '2',
						'rcpt' => '3',
						'timeLogged' => '7',
						'dsnAction' => '12',
						'jobId' => '4'
				);
				$cmd = 'head -n1 ' . $acct_folder . DIRECTORY_SEPARATOR . $file;
				$col_headers = explode(",", trim(shell_exec($cmd)));
				foreach ($col_headers as $key => $col_header) {
					if (trim($col_header) == 'bounceCat') {
						//$awk_cols['bounceCat'] = ($key + 1);
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
				$delivered_lines = explode("\n", trim(shell_exec($cmd)));
				if (!file_exists("/home/smtaftp/delivered/")) {
					mkdir("/home/smtaftp/delivered/", 0775);
				}
				foreach ($delivered_lines as $delivered_line) {
					$delivered_parts = explode("|", $delivered_line);
					if (($fh = fopen("/home/smtaftp/delivered/" . $delivered_parts[3] . ".txt", 'a')) !== false) {
						fwrite($fh, implode(",", $delivered_parts) . "\n");
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
		$acct_folder = '/var/log/pmta/delivered/';
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
		$pending_records = count(scandir('/var/log/pmta/delivered'));
		return parent::updatePendingRecordCount($pending_records);
	}
}