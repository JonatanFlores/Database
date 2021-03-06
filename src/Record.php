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

    /**
     * Constructor.
     * 
     * @param int $id
     */
    public function __construct($id = null)
    {
        if ($id) {
            $object = $this->load($id);

            if ($object) {
                $this->fromArray($object->toArray());
            }
        }
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

    /**
     * Remove the id when cloning the object
     */
    public function __clone()
    {
        unset($this->data[$this->getPrimaryKey()]);
    }

    /**
     * Get the table name
     * 
     * @param string
     */
    public function getEntity()
    {
        return static::TABLE_NAME;
    }

    /**
     * Get the primary key field name
     * 
     * @param string
     */
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
     * Load an object attributes by id
     * 
     * @param int $id
     */
    public function load($id)
    {
        $table = $this->getEntity();
        $primaryKey = $this->getPrimaryKey();

        // build SELECT instruction
        $sql = "SELECT * FROM {$table} WHERE {$primaryKey} = {$id}";

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

    /**
     * Alias for the load method and fill the object attributes by id
     * 
     * @param int $id
     */
    public function find($id)
    {
        $classname = get_called_class();
        $ar = new $classname;
        return $ar->load($id);
    }

    /**
     * Escape string, boolean and empty values
     * 
     * @param mixed $value
     */
    public function escape($value)
    {
        if (is_string($value) && !empty($value)) {
            // add back slashes
            $value = addslashes($value);
            return "'$value'";
        } elseif (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        } elseif ($value !== '') {
            return $value;
        } else {
            return 'NULL';
        }
    }

    /**
     * Escape an array of data, preventing some SQL injection
     * 
     * @param array $data
     * 
     * @return array
     */
    public function prepare($data)
    {
        $prepared = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $prepared[$key] = $this->escape($value);
            }
        }

        return $prepared;
    }

    /**
     * Persists data into database
     */
    public function store()
    {
        $prepared = $this->prepare($this->data);
        $id = $this->getPrimaryKey();

        // check if hasn't ID or if there isn't a database record
        if (empty($this->data[$id]) || !$this->load($this->{$id})) {
            if (empty($this->data[$id])) {
                $this->{$id} = $this->getLast() + 1;
                $prepared[$id] = $this->{$id};
            }

            // creates an SQL statement of INSERT
            $sql = "INSERT INTO {$this->getEntity()} ";
            $sql .= '('.implode(', ', array_keys($prepared)).')';
            $sql .= ' VALUES ';
            $sql .= '('.implode(', ', array_values($prepared)).')';
        } else {
            // build an SQL statement of UPDATE
            $sql = "UPDATE {$this->getEntity()}";
            $set = [];

            // build pares: column=value
            if ($prepared) {
                foreach ($prepared as $column => $value) {
                    if ($column !== $id) {
                        $set[] = "{$column} = {$value}";
                    }
                }
            }

            $sql .= ' SET '.implode(', ', $set);
            $sql .= " WHERE {$id} = " . (int) $this->data[$id];
        }

        // Get the current active transaction
        if ($conn = Transaction::get()) {
            // TODO generates a log of the SQL instruction
            // TODO use prepared statement
            $result = $conn->exec($sql);
            return $result;
        } else {
            throw new Exception('There isn\'t an active transaction');
        }
    }

    /**
     * Remove record from database by id or using the pre-loaded object.
     * 
     * @param int $id
     */
    public function delete($id = null)
    {
        $id = $id ? (int) $id : $this->id;
        $table = $this->getEntity();
        $primaryKey = $this->getPrimaryKey();

        // build the DELETE statement
        $sql = "DELETE FROM {$table} WHERE {$primaryKey} = {$id}";

        // Get the current active transaction
        if ($conn = Transaction::get()) {
            // TODO generates a log of the SQL instruction
            // TODO use prepared statement
            $result = $conn->exec($sql);
            return $result;
        } else {
            throw new Exception('There isn\'t an active transaction');
        }
    }

    /**
     * Get the last inserted id of the current 
     * table and active transaction
     */
    private function getLast()
    {
        if ($conn = Transaction::get()) {
            $sql = "SELECT MAX({$this->getPrimaryKey()}) FROM {$this->getEntity()}";

            // TODO generates a log for the SQL statement
            $result = $conn->query($sql);

            // return the database record data
            $row = $result->fetch();

            return $row[0];
        } else {
            throw new Exception('There isn\'t an active transaction');
        }
    }
}