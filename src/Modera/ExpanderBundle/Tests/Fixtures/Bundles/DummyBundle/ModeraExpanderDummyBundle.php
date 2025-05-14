<?php

namespace Modera\ExpanderBundle\Tests\Fixtures\Bundles\DummyBundle;

use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundleInterface;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Tests\Fixtures\Bundles\DummyBundle\DependencyInjection\ModeraExpanderDummyExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ModeraExpanderDummyBundle extends Bundle implements ExtensionPointsAwareBundleInterface
{
    public function build(ContainerBuilder $container): void
    {
        $container->registerExtension(new ModeraExpanderDummyExtension());

        $ep1 = new ExtensionPoint('modera_expander.dummy_resources');
        $container->addCompilerPass($ep1->createCompilerPass());

        $ep2 = new ExtensionPoint('modera_expander.blah_resources');
        $container->addCompilerPass($ep2->createCompilerPass());
    }

    public function getExtensionPointContributions(): array
    {
        return [
            'modera_expander.dummy_resources' => [
                'baz_resource',
            ],
        ];
    }
}
