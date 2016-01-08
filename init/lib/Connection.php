<?php
/**
 * Gets a basic database connection to use when installing
 */
class Connection {
    
    static $_instance;
    private $conn;
    private $mongo;
    private $database_array;
    
    /**
     * Returns an instance
     * @return Connection
     */
    static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new Connection();
        }
        return self::$_instance;
    }
    
    /**
     * Loads the databases
     * @param $filename
     */
    static function loadDatabasesFromFile($filename) {
        if (!file_exists($filename)) {
            throw new Exception('Cannot load databases from file ' . $filename);
        }
        $ini_file = parse_ini_file($filename, true);
        foreach ($ini_file['databases'] as $key => $value) {
            if (isset($ini_file[$value])) {
                if (isset($ini_file[$value]['param.user'])) { $db_array[$key]['user'] = $ini_file[$value]['param.user']; }
                if (isset($ini_file[$value]['param.password'])) { $db_array[$key]['password'] = $ini_file[$value]['param.password']; }
                if (isset($ini_file[$value]['param.database'])) { $db_array[$key]['database'] = $ini_file[$value]['param.database']; }
                if (isset($ini_file[$value]['param.host'])) { $db_array[$key]['host'] = $ini_file[$value]['param.host']; }
                if (isset($ini_file[$value]['param.port'])) { $db_array[$key]['port'] = $ini_file[$value]['param.port']; }
                if (isset($ini_file[$value]['param.dsn'])) { $db_array[$key]['dsn'] = $ini_file[$value]['param.dsn']; }
                self::getInstance()->setDatabaseArray($db_array);
            }
        }
    }
    
    /**
     * Returns a database connection
     * @return resource
     */
    function getDbConnection($name) {
        $db_array = $this->getDbConnectionArray($name);
        // determine how to get our parameters
        $method = isset($db_array['method']) ? $db_array['method'] : 'dsn';
        $database = isset($db_array['database']) ? $db_array['database'] : null;
        // get parameters
        switch($method) {
            case 'normal' :
                // get parameters normally
                $host     = isset($db_array['host']) ? $db_array['host'] : 'localhost';
                $port     = isset($db_array['port']) ? $db_array['port'] : '27017';
                $dsn = 'mongodb://' . $host;
                if ($port != '') {
                    $dsn .= ':' . $port;
                }
                break;
            case 'dsn' :
                $dsn = isset($db_array['dsn']) ? $db_array['dsn'] : null;
                if($dsn == null) {
                    // missing required dsn parameter
                    $error = 'Database configuration specifies method "dsn", but is missing dsn parameter';
                    throw new \DatabaseException($error);
                }
                break;
        }

        try {
            $options = array();

            if (isset($db_array['user'])) {
                $options['user'] = $db_array['user'];
            }
            if (isset($db_array['password'])) {
                $options['password'] = $db_array['password'];
            }
            
            $this->mongo = new \MongoClient($dsn, $options);
            // make sure the connection went through
            if ($this->mongo === false)
            {
                // the connection's foobar'
                $error = 'Failed to create a Mongo connection';
                throw new \DatabaseException($error);
            }
            
            // select our database
            if ($database != null) {
                $this->conn = $this->mongo->selectDB($database);
            }
            
            return $this->conn;
        } catch(\MongoException $e) {
            throw new \DatabaseException($e->getMessage());
        }
    }
    
    /**
     * Returns the database_array
     * @return array
     */
    function getDatabaseArray() {
        if (is_null($this->database_array)) {
            $this->database_array = array();
        }
        return $this->database_array;
    }
    /**
     * Sets the database_array
     * @param array
     */
    function setDatabaseArray($arg0) {
        $this->database_array = $arg0;
        return $this;
    }
    /**
     * Returns the db connection array
     * @return array
     */
    function getDbConnectionArray($name) {
        $db_array = $this->getDatabaseArray();
        if (isset($db_array[$name])) {
            return $db_array[$name];
        } else if (isset($db_array['default'])) {
            return $db_array['default'];
        }
        throw new Exception('No database defined as ' . $name . ' and no default database defined');
    }
    
    
    
}
