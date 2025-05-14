<?php

namespace Modera\ServerCrudBundle\Tests\Fixtures;

use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\Intercepting\ControllerActionsInterceptorInterface;

class DummyInterceptor implements ControllerActionsInterceptorInterface
{
    public array $invocations = [
        'create' => [],
        'update' => [],
        'get' => [],
        'list' => [],
        'remove' => [],
        'getNewRecordValues' => [],
    ];

    public function onCreate(array $params, AbstractCrudController $controller): void
    {
        $this->invocations['create'][] = [$params, $controller];
    }

    public function onUpdate(array $params, AbstractCrudController $controller): void
    {
        $this->invocations['update'][] = [$params, $controller];
    }

    public function onBatchUpdate(array $params, AbstractCrudController $controller): void
    {
        $this->invocations['batchUpdate'][] = [$params, $controller];
    }

    public function onGet(array $params, AbstractCrudController $controller): void
    {
        $this->invocations['get'][] = [$params, $controller];
    }

    public function onList(array $params, AbstractCrudController $controller): void
    {
        $this->invocations['list'][] = [$params, $controller];
    }

    public function onRemove(array $params, AbstractCrudController $controller): void
    {
        $this->invocations['remove'][] = [$params, $controller];
    }

    public function onGetNewRecordValues(array $params, AbstractCrudController $controller): void
    {
        $this->invocations['getNewRecordValues'][] = [$params, $controller];
    }
}
