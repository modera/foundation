<?php

namespace Modera\ServerCrudBundle\Tests\Fixtures\Bundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ServerCrudBundle\Tests\Fixtures\DummyInterceptor;

class ControllerActionInterceptorsProvider implements ContributorInterface
{
    public DummyInterceptor $interceptor;

    public function __construct()
    {
        $this->interceptor = new DummyInterceptor();
    }

    public function getItems(): array
    {
        return [
            $this->interceptor,
        ];
    }
}
