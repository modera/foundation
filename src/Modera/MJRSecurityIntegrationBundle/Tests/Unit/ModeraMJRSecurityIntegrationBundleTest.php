<?php

namespace Modera\MJRSecurityIntegrationBundle\Tests\Unit;

use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraMJRSecurityIntegrationBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild(): void
    {
        $builder = \Phake::mock(ContainerBuilder::class);
        \Phake::when($builder)
            ->addCompilerPass(\Phake::anyParameters())
            ->thenReturn($builder)
        ;

        $bundle = new ModeraMJRSecurityIntegrationBundle();

        $bundle->build($builder);

        \Phake::verify($builder, \Phake::times(1))
            ->addCompilerPass(
                $this->isInstanceOf(CompilerPassInterface::class)
            )
        ;
    }
}
