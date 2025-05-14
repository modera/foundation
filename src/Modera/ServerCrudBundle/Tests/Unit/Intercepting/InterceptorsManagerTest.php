<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Intercepting;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\Intercepting\InterceptorsManager;
use Modera\ServerCrudBundle\Intercepting\InvalidInterceptorException;
use Modera\ServerCrudBundle\Tests\Fixtures\DummyInterceptor;

require_once __DIR__.'/../../Fixtures/DummyInterceptor.php';

class InterceptorsManagerTest extends \PHPUnit\Framework\TestCase
{
    private InterceptorsManager $mgr;

    private ContributorInterface $provider;

    private AbstractCrudController $controller;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ContributorInterface::class);
        $this->mgr = new InterceptorsManager($this->provider);
        $this->controller = $this->createMock(AbstractCrudController::class);
    }

    public function testInvalidActionGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->mgr->intercept('xxx', [], $this->controller);
    }

    public function testInvalidInterceptorProvided(): void
    {
        $obj = new \stdClass();

        $this->provider->expects($this->atLeastOnce())
            ->method('getItems')
            ->will($this->returnValue([$obj]))
        ;

        $thrownException = null;
        try {
            $this->mgr->intercept('get', [], $this->controller);
        } catch (InvalidInterceptorException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertSame($obj, $thrownException->getInterceptor());
        $this->assertTrue('' != $thrownException->getMessage());
    }

    private function assertInvocation($interceptor, $type): void
    {
        $givenParams = ['foo', 'bar'];
        $givenController = $this->controller;

        $this->mgr->intercept($type, $givenParams, $givenController);

        $this->assertEquals(1, \count($interceptor->invocations[$type]));
        $this->assertSame($givenParams, $interceptor->invocations[$type][0][0]);
        $this->assertSame($givenController, $interceptor->invocations[$type][0][1]);
    }

    public function testIntercept(): void
    {
        $interceptor1 = new DummyInterceptor();

        $this->provider->expects($this->atLeastOnce())
            ->method('getItems')
            ->will($this->returnValue([$interceptor1]))
        ;

        $this->assertInvocation($interceptor1, 'create');
        $this->assertInvocation($interceptor1, 'get');
        $this->assertInvocation($interceptor1, 'update');
        $this->assertInvocation($interceptor1, 'list');
        $this->assertInvocation($interceptor1, 'remove');
        $this->assertInvocation($interceptor1, 'getNewRecordValues');
    }
}
