<?php

namespace Database;

final class Transaction
{
    /**
     * Active connection
     * 
     * @var \PDO $conn
     */
    private static $conn;

    /**
     * Constructor.
     */
    private function __construct() {}

    /**
     * Starts a transaction
     * 
     * @param string $connection
     */
    public function open($connection)
    {
        if (empty(self::$conn)) {
            self::$conn = Connection::open($connection);
            self::$conn->beginTransaction(); // starts transaction
        }
    }

    /**
     * Get the current transaction opened
     * 
     * @return \PDO
     */
    public function get()
    {
        return self::$conn;
    }

    /**
     * Close the current opened transaction
     */
    public function close()
    {
        if (self::$conn) {
            self::$conn->commit(); // apply operations
            self::$conn = null;
        }
    }

    /**
     * Revert all changes on the current transaction
     */
    public function rollback()
    {
        if (self::$conn) {
            self::$conn->rollback(); // reverte operations
            self::$conn = null;
        }
    }
}