<?php

namespace Modera\ConfigBundle\Tests\Unit\DependencyInjection;

use Modera\ConfigBundle\DependencyInjection\ModeraConfigExtension;
use Modera\ConfigBundle\Twig\TwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraConfigExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $ext = new ModeraConfigExtension();

        $builder = new ContainerBuilder();

        $ext->load([], $builder);

        $def = $builder->getDefinition(TwigExtension::class);
        $def->setClass(TwigExtension::class);

        $this->assertEquals(1, \count($def->getTag('twig.extension')));
    }
}
