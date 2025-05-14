<?php

namespace Modera\FileRepositoryBundle\Tests\Fixtures\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ModeraDummyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->setParameter('sys_temp_dir', \sys_get_temp_dir());
    }
}
