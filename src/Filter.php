<?php

namespace Database;

/**
 * Generates expressions to use in SQL filter operations. 
 * It will be used on SELECT, UPDATE AND DELETE statements
 */
class Filter extends Expression
{
    /** @var string $variable  */
    private $variable;

    /** @var string $operator */
    private $operator;

    /** @var mixed $value */
    private $value;

    /**
     * Constructor.
     * 
     * @param string $variable
     * @param string $operator
     * @param string $value
     */
    public function __construct($variable, $operator, $value)
    {
        // store the properties
        $this->variable = $variable;
        $this->operator = $operator;

        // transform the value according to a set of rules regarding type
        $this->value = $this->transform($value);
    }

    /**
     * Returns an string of the SQL expression generated
     * 
     * @return string
     */
    public function dump()
    {
        return "{$this->variable} {$this->operator} {$this->value}";
    }

    /**
     * Change the value `$value` argument according to its type
     * 
     * @param mixed $value
     * 
     * @return string
     */
    private function transform($value)
    {
        if (is_array($value)) {
            $tmp = [];

            foreach ($value as $item) {
                if (is_integer($item)) {
                    $tmp[] = $item;
                } elseif (is_string($item)) {
                    $tmp[] = "'{$item}'";
                }
            }
            // convert the array into an string separated by comma
            $result = '('.implode(', ', $tmp).')';
        } elseif (is_string($value)) {
            $result = "'{$value}'";
        } elseif (is_null($value)) {
            $result = 'NULL';
        } elseif (is_bool($value)) {
            $result = $value ? 'TRUE' : 'FALSE';
        } else {
            $result = $value;
        }

        // return the value
        return $result;
    }
}