<?php

namespace Database;

use Exception;

abstract class Record
{
    const TABLE_NAME = '';

    const PRIMARY_KEY = 'id';

    public function getEntity()
    {
        return static::TABLE_NAME;
    }

    public function getPrimaryKey()
    {
        return static::PRIMARY_KEY;
    }

    public function load($id)
    {
        // build SELECT instruction
        $sql = "SELECT * FROM {$this->getEntity()}";
        $sql .= " WHERE id={$this->getPrimaryKey()}";

        // get the current active transaction
        if ($conn = Transaction::get()) {
            // TODO Log a message of the sql
            $result = $conn->query($sql);

            // If returned any data
            if ($result) {
                // return the data as an object
                $object = $result->fetchObject(get_class($this));
            }

            return $object;
        } else {
            throw new Exception('There isn\'t an active transaction');
        }
    }
}