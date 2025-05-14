<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Security;

use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\Security\AccessDeniedHttpException;
use Modera\ServerCrudBundle\Security\SecurityControllerActionsInterceptor;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityControllerActionsInterceptorTest extends \PHPUnit\Framework\TestCase
{
    private AbstractCrudController $controller;

    private AuthorizationCheckerInterface $authorizationChecker;

    private SecurityControllerActionsInterceptor $interceptor;

    protected function setUp(): void
    {
        $this->controller = $this->createMock(AbstractCrudController::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->interceptor = new SecurityControllerActionsInterceptor($this->authorizationChecker);
    }

    public function testCheckAccess(): void
    {
        $config = [
            'security' => [
                'role' => 'ROLE_FOO',
            ],
        ];

        $this->teachController($config);

        $thrownException = null;
        try {
            $this->interceptor->checkAccess("it doesn't matter in this case", [], $this->controller);
        } catch (AccessDeniedHttpException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertEquals('ROLE_FOO', $thrownException->getRole());
    }

    private function teachController(array $preparedConfig): void
    {
        $this->controller->expects($this->atLeastOnce())
            ->method('getPreparedConfig')
            ->will($this->returnValue($preparedConfig))
        ;
    }

    private function teachAuthorizationChecker($expectedArgValue, $returnValue): void
    {
        $this->authorizationChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->with($this->equalTo($expectedArgValue))
            ->will($this->returnValue($returnValue))
        ;
    }

    public function assertExceptionThrown($actionName): void
    {
        $config = [
            'security' => [
                'actions' => [
                    'create' => 'ROLE_CREATE',
                    'update' => 'ROLE_UPDATE',
                    'get' => 'ROLE_GET',
                    'list' => 'ROLE_LIST',
                    'remove' => 'ROLE_REMOVE',
                    'getNewRecordValues' => 'ROLE_GRV',
                    'batchUpdate' => 'ROLE_BATCH_UPDATE',
                ],
            ],
        ];

        $this->teachAuthorizationChecker($config['security']['actions'][$actionName], false);
        $this->teachController($config);

        $thrownException = null;
        try {
            $this->interceptor->{'on'.\ucfirst($actionName)}([], $this->controller);
        } catch (AccessDeniedHttpException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertEquals($config['security']['actions'][$actionName], $thrownException->getRole());
    }

    private function assertAccessAllowed(string $actionName): void
    {
        $config = [
            'security' => [
                'actions' => [
                    'create' => 'ROLE_CREATE',
                    'update' => 'ROLE_UPDATE',
                    'get' => 'ROLE_GET',
                    'list' => 'ROLE_LIST',
                    'remove' => 'ROLE_REMOVE',
                    'getNewRecordValues' => 'ROLE_GRV',
                    'batchUpdate' => 'ROLE_BATCH_UPDATE',
                ],
            ],
        ];

        $this->teachAuthorizationChecker($config['security']['actions'][$actionName], true);
        $this->teachController($config);

        $this->interceptor->{'on'.\ucfirst($actionName)}([], $this->controller);
    }

    public function testOnCreateDenied(): void
    {
        $this->assertExceptionThrown('create');
    }

    public function testOnCreateAllowed(): void
    {
        $this->assertAccessAllowed('create');
    }

    public function testOnUpdate(): void
    {
        $this->assertExceptionThrown('update');
    }

    public function testOnUpdateAllowed(): void
    {
        $this->assertAccessAllowed('update');
    }

    public function testOnBatchUpdate(): void
    {
        $this->assertExceptionThrown('batchUpdate');
    }

    public function testOnBatchUpdateAllowed(): void
    {
        $this->assertAccessAllowed('batchUpdate');
    }

    public function testOnGet(): void
    {
        $this->assertExceptionThrown('get');
    }

    public function testOnGetAllowed(): void
    {
        $this->assertAccessAllowed('get');
    }

    public function testOnList(): void
    {
        $this->assertExceptionThrown('list');
    }

    public function testOnListAllowed(): void
    {
        $this->assertAccessAllowed('list');
    }

    public function testOnRemove(): void
    {
        $this->assertExceptionThrown('remove');
    }

    public function testOnRemoveAllowed(): void
    {
        $this->assertAccessAllowed('remove');
    }

    public function testOnGetNewRecordValues(): void
    {
        $this->assertExceptionThrown('getNewRecordValues');
    }

    public function testOnGetNewRecordValuesAllowed(): void
    {
        $this->assertAccessAllowed('getNewRecordValues');
    }

    public function testCheckAccessWithCallable(): void
    {
        $holder = new \stdClass();

        $config = [
            'security' => [
                'actions' => [
                    'create' => function (AuthorizationCheckerInterface $ac, array $params, string $actionName) use ($holder) {
                        $holder->ac = $ac;
                        $holder->params = $params;
                        $holder->actionName = $actionName;

                        return false;
                    },
                ],
            ],
        ];

        $this->teachController($config);

        $thrownException = null;
        try {
            $this->interceptor->checkAccess('create', ['foo'], $this->controller);
        } catch (AccessDeniedHttpException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertInstanceOf('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface', $holder->ac);
        $this->assertEquals(['foo'], $holder->params);
        $this->assertEquals('create', $holder->actionName);
    }
}
