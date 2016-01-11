<?php
namespace Smta\Link;

use \Smta\Drop;

class DropLog extends Drop {
    
    protected $log_contents;
    
    /**
     * Returns the log_contents
     * @return string
     */
    function getLogContents() {
        if (is_null($this->log_contents)) {
            $this->log_contents = "";
        }
        return $this->log_contents;
    }
    
    /**
     * Sets the log_contents
     * @var string
     */
    function setLogContents($arg0) {
        $this->log_contents = $arg0;
        $this->addModifiedColumn('log_contents');
        return $this;
    }
    
    /**
     * Updates the log from the log file
     * @return boolean
     */
    function updateLog() {
        if (file_exists($this->getLogFilename())) {
            // anything over 1MB only grab a tail of
            if (filesize($this->getLogFilename()) > 1048576) {
                $cmd = 'tail -n100 ' . $this->getLogFilename();
                $log_file_contents = trim(shell_exec($cmd));
                $log_file_contents = \Mojavi\Util\StringTools::consoleToHtmlColor($log_file_contents);
                $log_file_contents = '<span style="">-- Only showing the last 100 lines --<br />&nbsp;</span>' . $log_file_contents;
                $log_file_contents = '<div style="padding:10px;">' . $log_file_contents . '</div>';
                $this->setLogContents(nl2br($log_file_contents));
                return true;
            } else {
                $log_file_contents = file_get_contents($this->getLogFilename());
                $log_file_contents = \Mojavi\Util\StringTools::consoleToHtmlColor($log_file_contents);
                $log_file_contents = '<div style="padding:10px;">' . $log_file_contents . '</div>';
                $this->setLogContents(nl2br($log_file_contents));
                return true;
            }
        } else {
            $log_file_contents = '<div style="padding:10px;"><span>Log file is missing or import hasn\'t started yet<br />' . $this->getLogFilename() . '</span></div>';
            $this->setLogContents(nl2br($log_file_contents));
            return true;
        }
        return false;
    }
}