<?php

namespace Database;

use Exception;

final class Repository
{
    /** @var Record $activeRecord */
    private $activeRecord;

    /**
     * Constructor
     * 
     * @param Record $activeRecord
     */
    public function __construct(Record $activeRecord)
    {
        $this->activeRecord = $activeRecord;
    }

    /**
     * Load records from database with SELECT query by following 
     * the Criteria passed as argument
     * 
     * @param Criteria $criteria
     */
    public function load(Criteria $criteria)
    {
        $table = $this->activeRecord::TABLE_NAME;

        // generates an SELECT statement
        $sql = "SELECT * FROM {$table}";

        // get the where clause from the criteria object
        if ($criteria) {
            $expression = $criteria->dump();

            if ($expression) {
                $sql .= " WHERE {$expression}";
            }

            // get the criteria properties
            $order = $criteria->getProperty('order');
            $limit = $criteria->getProperty('limit');
            $offset = $criteria->getProperty('offset');

            if ($order) {
                $sql .= " ORDER BY {$order}";
            }

            if ($limit) {
                $sql .= " LIMIT {$limit}";
            }

            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        // get the current active transaction
        if ($conn = Transaction::get()) {
            // TODO Log a message of the sql

            // executes the query on the database
            $result = $conn->query($sql);
            $results = [];

            if ($result) {
                // loop through the records, returning an object
                while ($row = $result->fetchObject(get_class($this->activeRecord))) {
                    // stores the $results array
                    $results[] = $row;
                }
            }

            return $results;
        } else {
            throw new Exception('There isn\'t an active transaction');
        }
    }

    /**
     * Executes a DELETE query following the Criteria passed as argument
     * 
     * @param Criteria $criteria
     */
    public function delete(Criteria $criteria)
    {
        $table = $this->activeRecord::TABLE_NAME;
        $expression = $criteria->dump();
        $sql = "DELETE FROM {$table}";

        if ($expression) {
            $sql .= " WHERE {$expression}";
        }

        // get the current active transaction
        if ($conn = Transaction::get()) {
            // TODO Log a message of the sql

            // executes the DELETE query on the database
            $result = $conn->query($sql);

            return $result;
        } else {
            throw new Exception('There isn\'t an active transaction');
        }
    }

    /**
     * Count the records from the database by a given criteria
     * 
     * @param Criteria $criteria
     */
    public function count(Criteria $criteria)
    {
        $table = $this->activeRecord::TABLE_NAME;
        $expression = $criteria->dump();
        $sql = "SELECT COUNT(1) FROM {$table}";

        if ($expression) {
            $sql .= " WHERE {$expression}";
        }

        // get the current active transaction
        if ($conn = Transaction::get()) {
            // TODO Log a message of the sql

            // executes the SELECT query on the database
            $result = $conn->query($sql);
            $row = [];

            if ($result) {
                $row = $result->fetch();
            }

            return $row[0];
        } else {
            throw new Exception('There isn\'t an active transaction');
        }
    }
}