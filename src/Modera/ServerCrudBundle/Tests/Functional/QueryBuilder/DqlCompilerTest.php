<?php

namespace Modera\ServerCrudBundle\Tests\Functional\QueryBuilder;

use Doctrine\ORM\QueryBuilder;
use Modera\ServerCrudBundle\QueryBuilder\DoctrineQueryBuilderParametersBinder;
use Modera\ServerCrudBundle\QueryBuilder\DqlCompiler;
use Modera\ServerCrudBundle\QueryBuilder\ExpressionManager;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\Expression;
use Modera\ServerCrudBundle\Tests\Functional\AbstractTestCase;
use Modera\ServerCrudBundle\Tests\Functional\DummyUser;

class DqlCompilerTest extends AbstractTestCase
{
    private QueryBuilder $qb;

    private DqlCompiler $compiler;

    private ExpressionManager $exprMgr;

    private DoctrineQueryBuilderParametersBinder $binder;

    public function doSetUp(): void
    {
        $this->qb = self::$em->createQueryBuilder();
        $this->exprMgr = new ExpressionManager(DummyUser::class, self::$em);
        $this->binder = new DoctrineQueryBuilderParametersBinder($this->qb);
        $this->compiler = new DqlCompiler($this->exprMgr);
    }

    public function testCompileSimple(): void
    {
        $compileExpression = $this->compiler->compile(new Expression(':firstname', 'fn'), $this->binder);

        $this->assertEquals('e.firstname AS fn', $compileExpression);
    }

    public function testCompileFunction(): void
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

        $compiledExpression = $this->compiler->compile($expr, $this->binder);

        $this->assertEquals('CONCAT(e.firstname, CONCAT(?0, e.lastname)) AS fullname', $compiledExpression);
    }

    public function testCompileHiddenFunction(): void
    {
        $rawExpr = [
            'function' => 'CEIL',
            'args' => [
                ':price',
            ],
            'hidden' => true,
        ];
        $expr = new Expression($rawExpr, 'int_price');

        $compiledExpression = $this->compiler->compile($expr, $this->binder);

        $this->assertEquals('CEIL(e.price) AS HIDDEN int_price', $compiledExpression);
    }
}
