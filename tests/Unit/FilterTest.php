<?php

namespace Tests\Unit;

use Database\Expression;
use Database\Filter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FilterTest extends TestCase
{
    public function testFilterClassExtendsExpressionClass()
    {
        $this->assertInstanceOf(Expression::class, new Filter('date', '=', '2019-09-10'));
    }

    public function testItHasTheConstantsLogicOperators()
    {
        $filter = new ReflectionClass(Filter::class);
        $operators = $filter->getConstants();

        $this->assertArrayHasKey('AND_OPERATOR', $operators);
        $this->assertArrayHasKey('OR_OPERATOR', $operators);

        $this->assertEquals('AND ', $operators['AND_OPERATOR']);
        $this->assertEquals('OR ', $operators['OR_OPERATOR']);
    }

    public function testCanBuildFilters()
    {
        $filter1 = new Filter('date', '=', '2019-09-10');
        $filter2 = new Filter('salary', '>', 3000);
        $filter3 = new Filter('id', 'IN', [12, 15, 18]);

        $this->assertEquals("date = '2019-09-10'", $filter1->dump());
        $this->assertEquals('salary > 3000', $filter2->dump());
        $this->assertEquals('id IN (12, 15, 18)', $filter3->dump());
    }
}