<?php

namespace Modera\ServerCrudBundle\Tests\Fixtures\Bundle;

use Modera\ServerCrudBundle\Tests\Fixtures\Bundle\DependencyInjection\ModeraServerCrudDummyExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ModeraServerCrudDummyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerExtension(new ModeraServerCrudDummyExtension());
    }
}
