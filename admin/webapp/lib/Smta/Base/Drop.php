<?php
namespace Smta\Base;

use \Mojavi\Form\MongoForm;

class Drop extends MongoForm {
	
	const UPLOAD_FILE_TYPE_UPLOAD = 1;
	const UPLOAD_FILE_TYPE_FTP = 2;
	
	const BODY_TYPE_INLINE = 1;
	const BODY_TYPE_FILENAME = 2;
	
	const DELIMITER_TAB = 1;
	const DELIMITER_COMMA = 2;
	const DELIMITER_PIPE = 3;
	const DELIMITER_SEMICOLON = 4;
	const DELIMITER_COLON = 5;
	
	protected $drop_time;
	protected $name;
	protected $description;
	protected $list_file_location;
	protected $mapping;
	protected $from_domain;
	protected $report_stats;
	protected $delimiter;
	protected $header_array;
	protected $default_header_data_fields;
	protected $filename;
	protected $percent_complete;
	
	protected $body;
	protected $body_filename;
	protected $body_type;
	
	protected $is_error;
	protected $error_message;
	protected $log_filename;
	
	protected $is_ready_to_run;
	protected $is_ready_to_stop;
	protected $is_running;
	protected $is_drop_continuing;
	protected $force_drop_reset;
	protected $is_drop_finished;
	
	protected $bounce_file;
	protected $delivered_file;
	
	private $upload_file_type;
	
	/**
	 * Constructs new drop
	 * @return void
	 */
	function __construct() {
		$this->setCollectionName('drop');
		$this->setDbName('default');
	}
	
	/**
	 * Returns the drop_time
	 * @return \MongoDate
	 */
	function getDropTime() {
		if (is_null($this->drop_time)) {
			$this->drop_time = new \MongoDate();
		}
		return $this->drop_time;
	}
	
	/**
	 * Sets the drop_time
	 * @var \MongoDate
	 */
	function setDropTime($arg0) {
		$this->drop_time = $arg0;
		$this->addModifiedColumn("drop_time");
		return $this;
	}
	
	/**
	 * Returns the name
	 * @return string
	 */
	function getName() {
		if (is_null($this->name)) {
			$this->name = "";
		}
		return $this->name;
	}
	
	/**
	 * Sets the name
	 * @var string
	 */
	function setName($arg0) {
		$this->name = $arg0;
		$this->addModifiedColumn("name");
		return $this;
	}
	
	/**
	 * Returns the description
	 * @return string
	 */
	function getDescription() {
		if (is_null($this->description)) {
			$this->description = "";
		}
		return $this->description;
	}
	
	/**
	 * Sets the description
	 * @var string
	 */
	function setDescription($arg0) {
		$this->description = $arg0;
		$this->addModifiedColumn("description");
		return $this;
	}
	
	/**
	 * Returns the from_domain
	 * @return string
	 */
	function getFromDomain() {
		if (is_null($this->from_domain)) {
			$this->from_domain = "";
		}
		return $this->from_domain;
	}
	
	/**
	 * Sets the from_domain
	 * @var string
	 */
	function setFromDomain($arg0) {
		$this->from_domain = $arg0;
		$this->addModifiedColumn("from_domain");
		return $this;
	}
	
	/**
	 * Returns the filename
	 * @return string
	 */
	function getFilename() {
		if (is_null($this->filename)) {
			$this->filename = "";
		}
		return $this->filename;
	}
	
	/**
	 * Sets the filename
	 * @var string
	 */
	function setFilename($arg0) {
		$this->filename = $arg0;
		$this->addModifiedColumn("filename");
		return $this;
	}
	
	/**
	 * Returns the list_file_location
	 * @return string
	 */
	function getListFileLocation() {
		if (is_null($this->list_file_location)) {
			$this->list_file_location = "";
		}
		return $this->list_file_location;
	}
	
	/**
	 * Sets the list_file_location
	 * @var string
	 */
	function setListFileLocation($arg0) {
		$this->list_file_location = $arg0;
		$this->addModifiedColumn("list_file_location");
		return $this;
	}
	
	/**
	 * Returns the upload_file_type
	 * @return integer
	 */
	function getUploadFileType() {
		if (is_null($this->upload_file_type)) {
			$this->upload_file_type = self::UPLOAD_FILE_TYPE_UPLOAD;
		}
		return $this->upload_file_type;
	}
	
	/**
	 * Sets the upload_file_type
	 * @var integer
	 */
	function setUploadFileType($arg0) {
		$this->upload_file_type = (int)$arg0;
		$this->addModifiedColumn("upload_file_type");
		return $this;
	}
	
	/**
	 * Returns the delimiter
	 * @return integer
	 */
	function getDelimiter() {
		if (is_null($this->delimiter)) {
			$this->delimiter = self::DELIMITER_TAB;
		}
		return $this->delimiter;
	}
	
	/**
	 * Sets the delimiter
	 * @var integer
	 */
	function setDelimiter($arg0) {
		$this->delimiter = (int)$arg0;
		$this->addModifiedColumn("delimiter");
		return $this;
	}
	
	/**
	 * Returns the delimiter character
	 * @return string
	 */
	function getDelimiterCharacter($real_char = false) {
		if ($this->getDelimiter() == self::DELIMITER_COMMA) {
			return ",";
		} else if ($this->getDelimiter() == self::DELIMITER_TAB) {
			if ($real_char) {
				return "\t";
			} else {
				return '\t';
			}
		} else if ($this->getDelimiter() == self::DELIMITER_PIPE) {
			return "|";
		} else if ($this->getDelimiter() == self::DELIMITER_SEMICOLON) {
			return ";";
		} else if ($this->getDelimiter() == self::DELIMITER_COLON) {
			return ":";
		} else {
			return ",";
		}
	}
	
	/**
	 * Returns the body
	 * @return string
	 */
	function getBody() {
		if (is_null($this->body)) {
			$this->body = "";
		}
		return $this->body;
	}
	
	/**
	 * Sets the body
	 * @var string
	 */
	function setBody($arg0) {
		$this->body = $arg0;
		$this->addModifiedColumn("body");
		return $this;
	}
	
	/**
	 * Returns the body_filename
	 * @return string
	 */
	function getBodyFilename() {
		if (is_null($this->body_filename)) {
			$this->body_filename = "";
		}
		return $this->body_filename;
	}
	
	/**
	 * Sets the body_filename
	 * @var string
	 */
	function setBodyFilename($arg0) {
		$this->body_filename = $arg0;
		$this->addModifiedColumn("body_filename");
		return $this;
	}
	
	/**
	 * Returns the body_type
	 * @return integer
	 */
	function getBodyType() {
		if (is_null($this->body_type)) {
			$this->body_type = self::BODY_TYPE_INLINE;
		}
		return $this->body_type;
	}
	
	/**
	 * Sets the body_type
	 * @var integer
	 */
	function setBodyType($arg0) {
		$this->body_type = (int)$arg0;
		$this->addModifiedColumn("body_type");
		return $this;
	}
	
	/**
	 * Checks if the original upload file still exists.  If not, then we can't re-import the data and we can't re-map anything
	 * @return boolean
	 */
	function isOriginalFileExists() {
		if ($this->getIsFileUpload() == self::UPLOAD_FILE_TYPE_UPLOAD || $this->getIsFileUpload() == self::UPLOAD_FILE_TYPE_FTP) {
			if (file_exists($this->getFilename())) {
				return true;
			}
			return false;
		}
		// If this is a remote file, then assume it is there
		return true;
	}
	
	/**
	 * Returns the header_array
	 * @return array
	 */
	function getDefaultHeaderDataFields() {
		if (is_null($this->default_header_data_fields)) {
			$header_array = $this->getHeaderArray();
			if (!is_null($header_array)) {
				$this->default_header_data_fields = array();
				// Grab the first row and see if we can match up data fields
				$header_line = array_shift($header_array);
				if (is_array($header_line)) {
					foreach ($header_line as $key => $header_item) {
						$data_field = new \Smta\DataField();
						if (($data_field = $data_field->query(array('request_fields' => array('$in' => array($header_item))), false)) !== false) {
							$this->default_header_data_fields[$key] = array('field' => $data_field->getKey(), 'default_value' => '');
						}
					}
				} else {
					\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . "Header line is not an array: " . var_export($header_line, true));
				}
			}
		}
		return $this->default_header_data_fields;
	}
	
	/**
	 * Returns the header_array
	 * @return array
	 */
	function getHeaderArray($refresh_array = false) {
		if ($this->getUploadFileType() == self::UPLOAD_FILE_TYPE_UPLOAD) {
			if (($refresh_array && (trim($this->getFilename()) != '')) || (is_null($this->header_array) && (trim($this->getFilename()) != ''))) {
				$line_counter = 0;
				$this->header_array = array(0 => array());
				if (file_exists($this->getFilename())) {
					if (($fh = fopen($this->getFilename(), 'r')) !== false) {
						for ($i=0;$i<10;$i++) {
							if (($line = fgetcsv($fh, 4096, $this->getDelimiterCharacter(true))) !== false) {
								array_walk($line, function(&$value) { $value = utf8_encode($value); });
								$this->header_array[$i] = $line;
							}
						}
						fclose($fh);
					} else {
						\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . "Cannot open " . $this->getFilename() . " for reading");
						$this->header_array[0] = array_fill(0, 20, '');
					}
				} else {
					\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . "Cannot find file " . $this->getFilename());	
					$this->header_array[0] = array_fill(0, 20, '');
				}
			}
		} else if ($this->getUploadFileType() == self::UPLOAD_FILE_TYPE_FTP) {
			if (($refresh_array && (trim($this->getFilename()) != '')) || (is_null($this->header_array) && (trim($this->getFilename()) != ''))) {
				$line_counter = 0;
				$this->header_array = array(0 => array());
				if (file_exists($this->getFilename())) {
					\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: Opening file: " . $this->getFilename());
					if (($fh = fopen($this->getFilename(), 'r')) !== false) {
						for ($i=0;$i<10;$i++) {
							if (($line = fgetcsv($fh, 4096, $this->getDelimiterCharacter(true))) !== false) {
								\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: Reading line " . $i . ": " . var_export($line, true));
								array_walk($line, function(&$value) { $value = utf8_encode($value); });
								$this->header_array[$i] = $line;
							}
						}
						fclose($fh);
					} else {
						\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . "Cannot open " . $this->getFilename() . " for reading");
						$this->header_array[0] = array_fill(0, 20, '');
					}
				} else {
					\Mojavi\Logging\LoggerManager::error(__METHOD__ . " :: " . "Cannot find file " . $this->getFilename());
					$this->header_array[0] = array_fill(0, 20, '');
				}
			}
		} else {
			$this->header_array = array(0 => array());
			for ($i=0;$i<5;$i++) {
				$this->header_array[$i] = array_fill(0, 20, '');
			}
		}
		return $this->header_array;
	}
	
	/**
	 * Returns the mapping
	 * @return array
	 */
	function getMappingColumn($column = null) {
		$mapping = $this->getMapping();
		if (isset($mapping[$column])) {
			return $mapping[$column];
		}
		return new \Smta\Link\DropMapping();
	}
	
	/**
	 * Returns the is_error
	 * @return boolean
	 */
	function getIsError() {
		if (is_null($this->is_error)) {
			$this->is_error = false;
		}
		return $this->is_error;
	}
	
	/**
	 * Sets the is_error
	 * @var boolean
	 */
	function setIsError($arg0) {
		$this->is_error = (boolean)$arg0;
		$this->addModifiedColumn("is_error");
		return $this;
	}
	
	/**
	 * Returns the error_message
	 * @return string
	 */
	function getErrorMessage() {
		if (is_null($this->error_message)) {
			$this->error_message = "";
		}
		return $this->error_message;
	}
	
	/**
	 * Sets the error_message
	 * @var string
	 */
	function setErrorMessage($arg0) {
		$this->error_message = $arg0;
		$this->addModifiedColumn("error_message");
		return $this;
	}
	
	/**
	 * Returns the log_filename
	 * @return string
	 */
	function getLogFilename() {
		if (is_null($this->log_filename)) {
			$this->log_filename = "";
		}
		return $this->log_filename;
	}
	
	/**
	 * Sets the log_filename
	 * @var string
	 */
	function setLogFilename($arg0) {
		$this->log_filename = $arg0;
		$this->addModifiedColumn("log_filename");
		return $this;
	}
	
	/**
	 * Returns the mapping
	 * @return array
	 */
	function getMapping() {
		if (is_null($this->mapping)) {
			$this->mapping = array();
		}
		return $this->mapping;
	}
	
	/**
	 * Sets the mapping
	 * @var array
	 */
	function setMapping($arg0) {
		if (is_array($arg0)) {
			$this->mapping = $arg0;
			array_walk($this->mapping, function(&$value) {
				$mapping = new \Smta\Link\DropMapping();
				$mapping->populate($value);
				$value = $mapping;
			});
			$this->addModifiedColumn("mapping");
		}
		
		return $this;
	}
	
	/**
	 * Returns the report_stats
	 * @return \Smta\DropStats
	 */
	function getReportStats() {
		if (is_null($this->report_stats)) {
			$this->report_stats = new \Smta\Link\DropStats();
		}
		return $this->report_stats;
	}
	
	/**
	 * Sets the report_stats
	 * @var \Smta\DropStats
	 */
	function setReportStats($arg0) {
		if (is_array($arg0)) {
			$this->report_stats = $this->getReportStats();
			$this->report_stats->populate($arg0);
			$this->addModifiedColumn("report_stats");
		}
		return $this;
	}
	
	/**
	 * Returns the is_ready_to_run
	 * @return boolean
	 */
	function getIsReadyToRun() {
		if (is_null($this->is_ready_to_run)) {
			$this->is_ready_to_run = false;
		}
		return $this->is_ready_to_run;
	}
	
	/**
	 * Sets the is_ready_to_run
	 * @var boolean
	 */
	function setIsReadyToRun($arg0) {
		$this->is_ready_to_run = (boolean)$arg0;
		$this->addModifiedColumn("is_ready_to_run");
		return $this;
	}
	
	/**
	 * Returns the is_ready_to_stop
	 * @return boolean
	 */
	function getIsReadyToStop() {
		if (is_null($this->is_ready_to_stop)) {
			$this->is_ready_to_stop = false;
		}
		return $this->is_ready_to_stop;
	}
	
	/**
	 * Sets the is_ready_to_stop
	 * @var boolean
	 */
	function setIsReadyToStop($arg0) {
		$this->is_ready_to_stop = (boolean)$arg0;
		$this->addModifiedColumn("is_ready_to_stop");
		return $this;
	}
	
	/**
	 * Returns the is_running
	 * @return boolean
	 */
	function getIsRunning() {
		if (is_null($this->is_running)) {
			$this->is_running = false;
		}
		return $this->is_running;
	}
	
	/**
	 * Sets the is_running
	 * @var boolean
	 */
	function setIsRunning($arg0) {
		$this->is_running = $arg0;
		$this->addModifiedColumn("is_running");
		return $this;
	}
	
	/**
	 * Returns the is_drop_continuing
	 * @return boolean
	 */
	function getIsDropContinuing() {
		if (is_null($this->is_drop_continuing)) {
			$this->is_drop_continuing = false;
		}
		return $this->is_drop_continuing;
	}
	
	/**
	 * Sets the is_drop_continuing
	 * @var boolean
	 */
	function setIsDropContinuing($arg0) {
		$this->is_drop_continuing = $arg0;
		$this->addModifiedColumn("is_drop_continuing");
		return $this;
	}
	
	/**
	 * Returns the force_drop_reset
	 * @return boolean
	 */
	function getForceDropReset() {
		if (is_null($this->force_drop_reset)) {
			$this->force_drop_reset = false;
		}
		return $this->force_drop_reset;
	}
	
	/**
	 * Sets the force_drop_reset
	 * @var boolean
	 */
	function setForceDropReset($arg0) {
		$this->force_drop_reset = $arg0;
		$this->addModifiedColumn("force_drop_reset");
		return $this;
	}
	
	/**
	 * Returns the percent_complete
	 * @return integer
	 */
	function getPercentComplete() {
		if (is_null($this->percent_complete)) {
			$this->percent_complete = 0;
		}
		return $this->percent_complete;
	}
	
	/**
	 * Sets the percent_complete
	 * @var integer
	 */
	function setPercentComplete($arg0) {
		$this->percent_complete = (int)$arg0;
		$this->addModifiedColumn("percent_complete");
		return $this;
	}
	
	/**
	 * Returns the is_drop_finished
	 * @return boolean
	 */
	function getIsDropFinished() {
		if (is_null($this->is_drop_finished)) {
			$this->is_drop_finished = false;
		}
		return $this->is_drop_finished;
	}
	
	/**
	 * Sets the is_drop_finished
	 * @var boolean
	 */
	function setIsDropFinished($arg0) {
		$this->is_drop_finished = $arg0;
		$this->addModifiedColumn("is_drop_finished");
		return $this;
	}
	
	/**
	 * Returns the bounce_file
	 * @return string
	 */
	function getBounceFile() {
		if (is_null($this->bounce_file)) {
			$this->bounce_file = "/home/smtaftp/bounces/" . $this->getId() . ".txt";
		}
		return $this->bounce_file;
	}
	
	/**
	 * Sets the bounce_file
	 * @var string
	 */
	function setBounceFile($arg0) {
		$this->bounce_file = $arg0;
		$this->addModifiedColumn("bounce_file");
		return $this;
	}
	
	/**
	 * Returns the delivered_file
	 * @return string
	 */
	function getDeliveredFile() {
		if (is_null($this->delivered_file)) {
			$this->delivered_file = "/home/smtaftp/delivered/" . $this->getId() . ".txt";
		}
		return $this->delivered_file;
	}
	
	/**
	 * Sets the delivered_file
	 * @var string
	 */
	function setDeliveredFile($arg0) {
		$this->delivered_file = $arg0;
		$this->addModifiedColumn("delivered_file");
		return $this;
	}
	
	/**
	 * Inserts a new record
	 * @return integer
	 */
	function insert(array $criteria = array(), $hydrate = true) {
		// This is a file upload so process the $_FILES array
		if ($this->getUploadFileType() == self::UPLOAD_FILE_TYPE_UPLOAD) {
			// Upload of a file
			$insert_id = parent::insert($criteria, $hydrate);
			if ($insert_id !== false) {
				$this->setId($insert_id);
				if (isset($_FILES)) {
					if (count($_FILES) > 0) {
						if (!file_exists(MO_WEBAPP_DIR . '/meta/uploads/lists/')) {
							mkdir(MO_WEBAPP_DIR . '/meta/uploads/lists/', 0775, true);
							chown('apache', MO_WEBAPP_DIR . '/meta/uploads/lists/');
							chgrp('apache', MO_WEBAPP_DIR . '/meta/uploads/lists/');
							chmod(0775, MO_WEBAPP_DIR . '/meta/uploads/lists/');
						}
						foreach ($_FILES as $file) {
							$this->setListFileLocation($file["name"]);
							if (!move_uploaded_file($file['tmp_name'], MO_WEBAPP_DIR . '/meta/uploads/lists/list_upload_' . $insert_id)) {
								$this->setIsError(true);
								$this->setErrorMessage('Error uploading file, check permissions to ' . MO_WEBAPP_DIR . '/meta/uploads/lists/list_upload_' . $insert_id);
								$this->getErrors()->addError('error', 'Error uploading file, check permissions to /tmp/list_upload_' . $insert_id);
								parent::update();
							} else {
								$cmd = 'wc -l ' . MO_WEBAPP_DIR . '/meta/uploads/lists/list_upload_' . $insert_id . ' | awk \'{print $1}\'';
								$line_count = intval(shell_exec($cmd));
								$this->setReportStats(array('list_size' => $line_count));
								$this->setFilename(MO_WEBAPP_DIR . '/meta/uploads/lists/list_upload_' . $insert_id);
								parent::update();
							}
						}
					} else {
						$this->setIsError(true);
						$this->setErrorMessage('Error uploading file, uploaded file array is empty');
						$this->getErrors()->addError('error', 'Error uploading file, uploaded file array is empty');
						parent::update();
					}
				} else {
					$this->setIsError(true);
					$this->setErrorMessage('Error uploading file, no files were uploaded');
					$this->getErrors()->addError('error', 'Error uploading file, no files were uploaded');
					parent::update();
				}
			}
			return $insert_id;
		} else if ($this->getUploadFileType() == self::UPLOAD_FILE_TYPE_FTP) {
			// Local FTP upload
			$insert_id = parent::insert($criteria, $hydrate);
			if ($insert_id !== false) {
				$this->setId($insert_id);
				// Copy the file to a saved location
				if (file_exists($this->getListFileLocation())) {
					if (!file_exists(MO_WEBAPP_DIR . '/meta/uploads/lists/')) {
						mkdir(MO_WEBAPP_DIR . '/meta/uploads/lists/', 0775, true);
						chown('apache', MO_WEBAPP_DIR . '/meta/uploads/lists/');
						chgrp('apache', MO_WEBAPP_DIR . '/meta/uploads/lists/');
						chmod(0775, MO_WEBAPP_DIR . '/meta/uploads/lists/');
					}
					if (!copy($this->getListFileLocation(), MO_WEBAPP_DIR . '/meta/uploads/lists/list_upload_' . $insert_id)) {
						$this->setIsError(true);
						$this->setErrorMessage('Error uploading file, check permissions to ' . MO_WEBAPP_DIR . '/meta/uploads/lists/list_upload_' . $insert_id);
						$this->getErrors()->addError('error', 'Error uploading file, check permissions to /tmp/list_upload_' . $insert_id);
						parent::update();
					} else {
						$cmd = 'wc -l ' . MO_WEBAPP_DIR . '/meta/uploads/lists/list_upload_' . $insert_id . ' | awk \'{print $1}\'';
						$line_count = intval(shell_exec($cmd));
						$this->setReportStats(array('list_size' => $line_count));
						$this->setFilename(MO_WEBAPP_DIR . '/meta/uploads/lists/list_upload_' . $insert_id);
						parent::update();
					}
				} else {
					$this->setIsError(true);
					$this->setErrorMessage('Error uploading file, no files were uploaded');
					$this->getErrors()->addError('error', 'Error uploading file, no files were uploaded');
					parent::update();
				}
			}
			return $insert_id;
		}
	}
	
	/**
	 * Updates the mapping for this import record
	 * @return integer
	 */
	function updateMapping() {
		return parent::update();
	}
}