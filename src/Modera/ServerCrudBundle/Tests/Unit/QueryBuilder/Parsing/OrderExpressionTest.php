<?php

namespace Modera\ServerCrudBundle\Tests\Unit\QueryBuilder\Parsing;

use Modera\ServerCrudBundle\QueryBuilder\Parsing\OrderExpression;

class OrderExpressionTest extends \PHPUnit\Framework\TestCase
{
    public function testParsingBasics(): void
    {
        $expr = new OrderExpression(['property' => 'foo', 'direction' => 'ASC']);

        $this->assertEquals('foo', $expr->getProperty());
        $this->assertEquals('ASC', $expr->getDirection());

        $expr = new OrderExpression([]);

        $this->assertNull($expr->getProperty());
        $this->assertNull($expr->getDirection());
    }

    public function testValidation(): void
    {
        $expr = new OrderExpression(['property' => 'foo', 'direction' => 'ASC']);

        $this->assertTrue($expr->isValid());

        $expr = new OrderExpression(['property' => 'foo.bar', 'direction' => 'DESC']);

        $this->assertTrue($expr->isValid());

        $expr = new OrderExpression(['property' => 'foo', 'direction' => 'XXX']);

        $this->assertFalse($expr->isValid());

        $expr = new OrderExpression([]);

        $this->assertFalse($expr->isValid());

        $expr = new OrderExpression(['property' => null, 'direction' => null]);

        $this->assertFalse($expr->isValid());

        $expr = new OrderExpression(['property' => 'foo']);

        $this->assertFalse($expr->isValid());

        $expr = new OrderExpression(['direction' => 'ASC']);

        $this->assertFalse($expr->isValid());

        $expr = new OrderExpression(['property' => 'xxx', 'direction' => 'asc']);

        $this->assertTrue($expr->isValid());
    }
}
