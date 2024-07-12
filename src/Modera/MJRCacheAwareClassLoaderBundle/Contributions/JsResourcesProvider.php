<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Contributes a javascript link to dynamically generated extjs-class-loader overriding logic.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->urlGenerator = $router;
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
