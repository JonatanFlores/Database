<?php

namespace Tests\Unit;

use Database\Expression;
use Database\Filter;
use Database\Criteria;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CriteriaTest extends TestCase
{
    public function testFilterClassExtendsExpressionClass()
    {
        $this->assertInstanceOf(Expression::class, new Criteria);
    }

    public function testItHasTheConstantsLogicOperators()
    {
        $filter = new ReflectionClass(Criteria::class);
        $operators = $filter->getConstants();

        $this->assertArrayHasKey('AND_OPERATOR', $operators);
        $this->assertArrayHasKey('OR_OPERATOR', $operators);

        $this->assertEquals('AND ', $operators['AND_OPERATOR']);
        $this->assertEquals('OR ', $operators['OR_OPERATOR']);
    }

    public function testCanAddExpressionsToBuildCriterias()
    {
        $criteria = new Criteria;

        $criteria->add(new Filter('date', '>', '2019-09-10'));

        $this->assertEquals("(date > '2019-09-10')", $criteria->dump());
    }

    public function testCanBuildComplexExpressionWithAndOperatorAsDefault()
    {
        $criteria = new Criteria;

        $criteria->add(new Filter('date', '>=', '2019-09-10'));
        $criteria->add(new Filter('date', '<=', '2019-09-20'));

        $this->assertEquals("(date >= '2019-09-10' AND date <= '2019-09-20')", $criteria->dump());
    }

    public function testCanBuildComplexExpressionWithAndOperatorPassedAsArgument()
    {
        $criteria = new Criteria;

        $criteria->add(new Filter('date', '>=', '2019-09-10'));
        $criteria->add(new Filter('date', '<=', '2019-09-20'), Expression::AND_OPERATOR);

        $this->assertEquals("(date >= '2019-09-10' AND date <= '2019-09-20')", $criteria->dump());
    }

    public function testCanBuildComplexExpressionWithOrOperatorPassedAsArgument()
    {
        $criteria = new Criteria;

        $criteria->add(new Filter('date', '>=', '2019-09-10'));
        $criteria->add(new Filter('salary', '>', 2000), Expression::OR_OPERATOR);

        $this->assertEquals("(date >= '2019-09-10' OR salary > 2000)", $criteria->dump());
    }

    public function testCanBuildAnExpressionByPassingAnArrayOfValues()
    {
        $criteria = new Criteria;

        $criteria->add(new Filter('id', 'IN', [12, 16, 20]));

        $this->assertEquals('(id IN (12, 16, 20))', $criteria->dump());
    }

    public function testCanSetPropertyWithValue()
    {
        $criteria = new Criteria;
        $criteria->setProperty('ORDER', 'id DESC');
        $criteria->setProperty('LIMIT', 10);

        $this->assertEquals('id DESC', $criteria->getProperty('ORDER'));
        $this->assertEquals(10, $criteria->getProperty('LIMIT'));
    }

    public function testCanSetPropertyWithoutValue()
    {
        $criteria = new Criteria;
        $criteria->setProperty('ORDER');
        $criteria->setProperty('LIMIT');

        $this->assertNull($criteria->getProperty('ORDER'));
        $this->assertNull($criteria->getProperty('LIMIT'));
    }
}