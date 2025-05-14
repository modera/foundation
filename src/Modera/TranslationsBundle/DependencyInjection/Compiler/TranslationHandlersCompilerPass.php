<?php

namespace Modera\TranslationsBundle\DependencyInjection\Compiler;

use Modera\TranslationsBundle\Service\TranslationHandlersChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @copyright 2014 Modera Foundation
 */
class TranslationHandlersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(TranslationHandlersChain::class)) {
            return;
        }

        $definition = $container->getDefinition(TranslationHandlersChain::class);

        $taggedServices = $container->findTaggedServiceIds(
            'modera_translations.translation_handler'
        );
        foreach ($taggedServices as $id => $attributes) {
            if ($container->getDefinition($id)->isAbstract()) {
                continue;
            }

            $definition->addMethodCall(
                'addHandler',
                [new Reference($id)],
            );
        }
    }
}
