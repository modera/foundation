<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Contributes a javascript link to dynamically generated extjs-class-loader overriding logic.
 *
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.js_resources')]
class JsResourcesProvider implements ContributorInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getItems(): array
    {
        return [
            [
                'order' => PHP_INT_MIN + 10,
                'resource' => $this->urlGenerator->generate('modera_mjr_cache_aware_class_loader'),
            ],
        ];
    }
}
