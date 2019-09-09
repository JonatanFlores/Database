<?php

namespace Database;

use Database\Exceptions\ConfigFileNotFoundException;
use Database\Exceptions\InvalidConfigFileException;
use PDO;

/**
 * Connection class
 */
final class Connection
{
    /** @var string $path */
    private static $path;

    /** @var array $config */
    private static $config = [];

    /**
     * Load configuration file stored as array into `$config` variable
     * 
     * @param string $path
     */
    public static function load($path)
    {
        if (!is_file($path)) {
            throw new ConfigFileNotFoundException("File not found at: {$path}");
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new InvalidConfigFileException("Config file MUST HAVE an Array of database connections");
        }

        static::$path = $path;
        static::$config = $config;
    }

    /**
     * Get the database config file path
     * 
     * @return string
     */
    public static function getPath()
    {
        return static::$path;
    }

    /**
     * Generate instances of PDO class for the following databases:
     * PostgreSQL, MySQL, Sqlite, IBase, Oracle, MSSql:
     * 
     * Drivers:
     * 
     * - PostgreSQL: pgsql
     * - MySQL: mysql
     * - Sqlite: sqlite
     * - IBase: ibase
     * - Oracle: oci8
     * - MSSql: mssql
     * 
     * @param string $name
     * 
     * @return PDO
     */
    public function open($name)
    {
        $db = static::$config[$name];
        
        // Get database connection information
        $user = isset($db['user']) ? $db['user'] : null;
        $pass = isset($db['pass']) ? $db['pass'] : null;
        $name = isset($db['name']) ? $db['name'] : null;
        $host = isset($db['host']) ? $db['host'] : null;
        $type = isset($db['type']) ? $db['type'] : null;
        $port = isset($db['port']) ? $db['port'] : null;

        // Find out the database driver
        switch ($type) {
            case 'pgsql':
                $port = $port ? $port : 5432;
                $conn = new PDO("pgsql:dbname={$name}; user={$user}; password={$pass}; host={$host}; port={$port}");
                break;
            case 'mysql':
                $port = $port ? $port : 3306;
                $conn = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass);
                break;
            case 'sqlite':
                $conn = new PDO("sqlite:{$name}");
                break;
            case 'ibase':
                $conn = new PDO("firebird:dbname={$name}", $user, $pass);
                break;
            case 'oci8':
                $conn = new PDO("oci:dbname={$name}", $user, $pass);
                break;
            case 'mssql':
                $conn = new PDO("mssql:host={$host},1433;dbname={$name}", $user, $pass);
                break;
        }

        // Make PDO throw exceptions
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}