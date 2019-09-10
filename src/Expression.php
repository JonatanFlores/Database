<?php

namespace Database;

abstract class Expression
{
    // logic operators
    const AND_OPERATOR = 'AND';
    const OR_OPERATOR = 'OR';

    abstract public function dump();
}