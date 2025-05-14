<?php

namespace Modera\ServerCrudBundle\Tests\Unit\QueryBuilder\Parsing;

use Modera\ServerCrudBundle\QueryBuilder\Parsing\Expression;

class ExpressionTest extends \PHPUnit\Framework\TestCase
{
    public function testSimpleExpressionWithoutAlias(): void
    {
        $expr = new Expression('firstname');

        $this->assertEquals('firstname', $expr->getExpression());
        $this->assertNull($expr->getAlias());
        $this->assertNull($expr->getFunction());
    }

    public function testSimpleExpressionWithAlias(): void
    {
        $expr = new Expression('firstname', 'fn');

        $this->assertEquals('firstname', $expr->getExpression());
        $this->assertEquals('fn', $expr->getAlias());
        $this->assertNull($expr->getFunction());
    }

    public function testFunctionCallExpressionWithAlias(): void
    {
        $rawExpr = [
            'function' => 'CONCAT',
            'args' => [
                ':firstname',
                [
                    'function' => 'CONCAT',
                    'args' => [' ', ':lastname'],
                ],
            ],
        ];
        $expr = new Expression($rawExpr, 'fullname');

        $this->assertEquals('fullname', $expr->getAlias());
        $this->assertSame($rawExpr, $expr->getExpression());
        $this->assertEquals($rawExpr['function'], $expr->getFunction());
        $this->assertTrue(\is_array($expr->getFunctionArgs()));
        $args1 = $expr->getFunctionArgs();
        $this->assertEquals(2, \count($args1));
        $this->assertEquals(':firstname', $args1[0]);
        $this->assertInstanceOf(Expression::class, $args1[1]);
        /** @var Expression $fetchSubArg */
        $fetchSubArg = $args1[1];
        $this->assertNull($fetchSubArg->getAlias());
        $args2 = $fetchSubArg->getFunctionArgs();
        $this->assertTrue(\is_array($args2));
        $this->assertEquals(2, \count($args2));
        $this->assertEquals(' ', $args2[0]);
        $this->assertEquals(':lastname', $args2[1]);
    }

    public function testHowWellFunctionNameIsValidated(): void
    {
        $this->expectException(\RuntimeException::class);

        new Expression(['function' => '; SELECT']);
    }

    public function testSanitizeAlias(): void
    {
        $expr = new Expression('foo', '; DELETE FROM xxx');

        $this->assertNull($expr->getAlias());
    }
}
