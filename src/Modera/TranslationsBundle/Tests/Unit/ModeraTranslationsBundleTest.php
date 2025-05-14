<?php

namespace Modera\TranslationsBundle\Tests\Unit;

use Modera\TranslationsBundle\DependencyInjection\Compiler\TranslationHandlersCompilerPass;
use Modera\TranslationsBundle\ModeraTranslationsBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraTranslationsBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testTranslationHandlersCompilerPass(): void
    {
        $containerBuilder = \Phake::mock(ContainerBuilder::class);
        \Phake::when($containerBuilder)
            ->addCompilerPass(\Phake::anyParameters())
            ->thenReturn($containerBuilder)
        ;

        $bundle = new ModeraTranslationsBundle();
        $bundle->build($containerBuilder);

        \Phake::verify($containerBuilder)->addCompilerPass(\Phake::capture($pass));

        $this->assertInstanceOf(TranslationHandlersCompilerPass::class, $pass);
    }
}
