<?php

namespace app\helpers;

class Db
{
    private $prefix	= '';
    private $stmt;
    private $dbh;
    private $driver_options;
    private $logger;
    public function __construct($settings = [])
    {
        if (isset($settings['prefix'])) {
            $this->prefix = $settings['prefix'];
        }
        /**
         * Set DSN
         */
        $dsn = 'mysql:host='. $settings['host'] . ';dbname=' . $settings['dbname'];
        /**
         * Set options
         */
        $this->driver_options = [
            \PDO::ATTR_EMULATE_PREPARES		=> false,
            \PDO::ATTR_ERRMODE				=> \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE	=> \PDO::FETCH_ASSOC,
            \PDO::ATTR_PERSISTENT			=> true,
            \PDO::MYSQL_ATTR_INIT_COMMAND	=> 'SET NAMES utf8'
        ];
        try {
            $this->dbh = new \PDO($dsn, $settings['username'], $settings['password'], $this->driver_options);
        } catch(\PDOException $e) {
            throw new \Exception($e->getMessage());
        }

        /**
         * Checking connection to database
         */
        if (is_null($this->dbh)) {
            throw new \Exception('Unable to establish a connection to database.');
        }
    }
    /**
     * Make a query
     * @param string $query
     * @return PDOStatement|PDOException
     */
    public function query($query)
    {
        $time['start'] = microtime(true);
        $query = str_replace('@table.', $this->prefix, $query);
        $this->stmt = $this->dbh->prepare($query);
        $time['end'] = microtime(true);
        $time['elapsed'] = $time['end'] - $time['start'];
        $this->logger[] = round($time['elapsed'], 16);
        return $this->stmt;
    }
    /**
     * Bind the data
     * @param string $param
     * @param string $value
     * @param int|bool|null|string $type
     * @return bool
     */
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = \PDO::PARAM_NULL;
                    break;
                default:
                    $type = \PDO::PARAM_STR;
                    break;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Bind the data from an array
     * @param array $param_array
     * @return bool
     * @see Db::bind()
     */
    public function bindArray($param_array)
    {
        array_map(array($this, 'bind'), array_keys($param_array), array_values($param_array));
    }
    /**
     * Executhe the query
     * @return bool
     */
    public function execute()
    {
        return $this->stmt->execute();
    }
    /**
     * Get multiple records
     * @return array
     * @see Db::$driver_options
     */
    public function fetchAll()
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    /**
     * Get single record
     * @return object
     * @see Db::$driver_options
     */
    public function fetch()
    {
        $this->execute();
        return $this->stmt->fetch();
    }
    /**
     * Get number of affected rows
     * @return int
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }
    /**
     * Get last inserted id
     * @return int
     */
    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
    /**
     * Run batch queries
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->dbh->beginTransaction();
    }
    /**
     * Stop batch queries
     * @return bool
     */
    public function endTransaction()
    {
        return $this->dbh->commit();
    }
    /**
     * Cancel batch queries
     * @return bool
     */
    public function cancelTransaction()
    {
        return $this->dbh->rollBack();
    }

    /**
     * Dumps info contained in prepared statement
     * @return void
     */
    public function debugDumpParams()
    {
        return $this->stmt->debugDumpParams();
    }

    /**
     * Gets the count of queries and its running time
     * @return object
     * @todo Log other errors produced in the class
     */
    public function debugSQL()
    {

        $log = new \stdClass;
        $log->time = 0;
        $log->queries = 0;

        if (isset($this->logger)) {
            foreach ($this->logger as $time) {
                $log->time += $time;
            }
        }

        $log->queries += count($this->logger);

        return $log;
    }
}