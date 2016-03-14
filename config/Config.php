<?php
namespace config;

/**
 * Class Config
 *
 * @package config
 */
class Config
{
    /**
     * @var null|Config Instance of application
     */
    private static $_instance = null;

    /**
     * @var \PDO Connect to the database
     */
    public $_db;

    private function __construct()
    {
    } // Close constructor. Can be called only from getInstance()

    private function __clone()
    {
    } // Disallow cloning

    /**
     * Get instance of the application
     *
     * @static
     * @return Config
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Config();
        }

        return self::$_instance;
    }

    /**
     * Set into $this->_db new \PDO connection to database
     *
     * Example:
     * App::$app->dbConnect('tires');
     * $this->db = App::$app->_db;
     *
     * @param string $dbName Database name to connection
     */
    public function dbConnect($dbName)
    {
        if (! is_object($this->_db)) {
            $dsn     = 'mysql:host=' . App::DB_HOST . ';dbname=' . $dbName . ';charset=utf8';
            $options = [
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
            ];

            try {
                $this->_db = new \PDO($dsn, App::DB_USER, App::DB_PASS, $options);
            } catch (\PDOException $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }
        }
    }
}
