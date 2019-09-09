<?php

namespace Database;

use Exception;

abstract class Record
{
    /**
     * Database table
     * 
     * @var string TABLE_NAME
     */
    const TABLE_NAME = '';

    /**
     * Database primary key field
     * 
     * @var string PRIMARY_KEY
     */
    const PRIMARY_KEY = 'id';

    /**
     * Object data
     * 
     * @var array $data
     */
    protected $data;

    public function getEntity()
    {
        return static::TABLE_NAME;
    }

    public function getPrimaryKey()
    {
        return static::PRIMARY_KEY;
    }

    /**
     * Fill the object properties by passing an associative array
     * 
     * @param array $data
     */
    public function fromArray($data)
    {
        $this->data = $data;
    }

    /**
     * Return object properties as an associative array
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Dinamically set attribute data through the fromArray method
     * 
     * @param string $prop
     * @param mixed $value
     */
    public function __set($prop, $value)
    {
        if (method_exists($this, "set{$prop}")) {
            // run the set<Property> method
            call_user_func([$this, "set{$prop}"], $value);
        } else {
            if ($value === null) {
                unset($this->data[$prop]);
            } else {
                // add the value to the corresponding property
                $this->data[$prop] = $value;
            }
        }
    }

    /**
     * Dinamically call attribute
     * 
     * @param string $prop
     * 
     * @return mixed
     */
    public function __get($prop)
    {
        if (method_exists($this, "get{$prop}")) {
            // call the get<Property> method
            return call_user_func([$this, "get{$prop}"]);
        } else {
            if (isset($this->data[$prop])) {
                return $this->data[$prop];
            }
        }
    }

    /**
     * Dinamically check if key exists in $data attribute
     * 
     * @param string $prop
     * 
     * @return mixed
     */
    public function __isset($prop)
    {
        return isset($this->data[$prop]);
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