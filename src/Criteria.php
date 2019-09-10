<?php

namespace Database;

class Criteria extends Expression
{
    /** @var array $expressions */
    private $expressions;

    /** @var array $operators */
    private $operators;

    /** @var array $properties */
    private $properties;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->expressions = [];
        $this->operators = [];
    }

    /**
     * Add expressions for building SQL filters
     * 
     * @param Expression $expression
     * @param string $operator
     */
    public function add(Expression $expression, $operator = self::AND_OPERATOR)
    {
        // at the first time, we don't need to concatenate it
        if (empty($this->expressions)) {
            $operator = null;
        }

        // agregate the expression's result into the expression's list
        $this->expressions[] = $expression;
        $this->operators[] = $operator;
    }

    /**
     * Returns the SQL filters (expressions) generated
     * 
     * @return string
     */
    public function dump()
    {
        // concatenate the list of expressions
        if (is_array($this->expressions)) {
            if (count($this->expressions) > 0) {
                $result = '';
                foreach ($this->expressions as $i => $expression) {
                    $operator = $this->operators[$i];
                    // concatenate the operater with the respective expression
                    $result .= $operator.$expression->dump() . ' ';
                }
                $result = trim($result);
                return "({$result})";
            }
        }
    }

    /**
     * Defines characteristics of the SQL instructions, such as: 
     * ORDER, LIMIT, OFFSET etc.
     * 
     * @param string $property
     * @param mixed $value
     */
    public function setProperty($property, $value = null)
    {
        if (isset($value)) {
            $this->properties[$property] = $value;
        } else {
            $this->properties[$property] = null;
        }
    }

    /**
     * Gets SQL characteristics previously defined by the `setProperty` method
     * 
     * @return mixed
     */
    public function getProperty($property)
    {
        if (isset($this->properties[$property])) {
            return $this->properties[$property];
        }
    }
}