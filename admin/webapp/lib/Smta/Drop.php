<?php
namespace Smta;

class Drop extends Base\Drop {
	
	/**
	 * Updates the drop to start
	 * @return integer
	 */
	function updateStartDrop($force_restart = false) {
		// Unset the pid_import
		$this->update(array('_id' => $this->getId()), array('$unset' => array('pid_import' => 1)));
		// Also reset all the other fields
		$this->setErrorMessage("");
		$this->setIsError(false);
		$this->setIsReadyToRun(true);
		$this->setIsDropFinished(false);
		$this->setIsRunning(false);
		if ($force_restart) {
			$this->setPercentComplete(0);
			$this->getReportStats()->resetReportingStats();
			$this->setForceDropReset(true);
			$this->setIsDropContinuing(false);
		} else {
			$this->setIsRunning(false);
			$this->setIsDropContinuing(true);
			$this->setForceDropReset(false);
		}
		return parent::update();
	}
	
	/**
	 * Updates the drop to stop
	 * @return integer
	 */
	function updateStopDrop() {
		// Unset the pid_import
		$this->update(array('_id' => $this->getId()), array('$unset' => array('pid_import' => 1)));
		$this->update(array('_id' => $this->getId()), 
			array('$set' => array(
				'is_ready_to_run' => false,
				'is_ready_to_stop' => true,
				'is_drop_finished' => true,
				'is_running' => false
			))
		);
		return true;
	}
	
	/**
	 * Updates the percent complete for this drop
	 * @return integer $percent_complete
	 */
	function updatePercent($percent_complete) {
		$this->update(array('_id' => $this->getId()), array('$set' => array('percent_complete' => $percent_complete)));
		$this->setPercentComplete($percent_complete);
		return true;
	}
}