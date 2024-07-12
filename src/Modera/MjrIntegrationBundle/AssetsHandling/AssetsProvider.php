<?php

namespace Modera\MjrIntegrationBundle\AssetsHandling;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Brings support for differentiation between blocking and non-blocking assets. The difference between these two
 * is that those which are blocking have be be loaded into browser before user can interact with backend and the latter
 * ones can be loaded later. To begin with it is going to be used by ModeraMJRSecurityIntegrationBundle
 * bundle, making it possible to load a backend page as fast as possible (just render a login panel) and once page
 * is loaded it will start loading css/javascript which are going to be used only when user has already logged in.
 *
 * For an asset to become a non-blocking it name must start with *. For example, you may contribute to
 * "modera_mjr_integration.css_resources" extension point with a non-blocking asset foo.css:
 *
 *     class CssResourcesProvider implements ContributorInterface
 *     {
 *         public function getItems()
 *         {
 *             return [
 *                 '*foo.css'
 *             ];
 *         }
 *     }
 *
 * Support for * will be dropped as of MF 3.0 and all assets will become non-blocking if ! suffix is not used.
 * Please see MF-UPGRADE-3.0.md file from https://github.com/modera/foundation repository for more detailed information
 * about this. Also, take a look at filterRawAssetsByType method.
 *
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class AssetsProvider implements AssetsProviderInterface
{
    private ContributorInterface $cssResourcesProvider;

    private ContributorInterface $jsResourcesProvider;

    public function __construct(ContainerInterface $container)
    {
        // we cannot inject them directly, because these services are built dynamically

        /** @var ContributorInterface $cssResourcesProvider */
        $cssResourcesProvider = $container->get('modera_mjr_integration.css_resources_provider');
        $this->cssResourcesProvider = $cssResourcesProvider;

        /** @var ContributorInterface $jsResourcesProvider */
        $jsResourcesProvider = $container->get('modera_mjr_integration.js_resources_provider');
        $this->jsResourcesProvider = $jsResourcesProvider;
    }

    private function validateType(string $type): void
    {
        if (!\in_array($type, [self::TYPE_NON_BLOCKING, self::TYPE_BLOCKING])) {
            throw new \InvalidArgumentException("Invalid type '$type' given.");
        }
    }

    /**
     * Filters given $rawAssets and depending on given $type either returns those who
     * are blocking or non-blocking.
     *
     * @param array<string|array{'resource': string, 'order'?: int}> $rawAssets
     *
     * @return string[]
     */
    private function filterRawAssetsByType(string $type, array $rawAssets): array
    {
        $this->validateType($type);

        $result = [
            self::TYPE_BLOCKING => [],
            self::TYPE_NON_BLOCKING => [],
        ];

        // As of release of 3.0 support for * syntax will be dropped and all resources by default will be considered
        // non-blocking and to mark your resource as blocking you will have to use ! suffix, for example:
        // !my-blocking-script.js
        foreach ($rawAssets as $resource) {
            $order = 0;
            if (\is_array($resource)) {
                if (isset($resource['order'])) {
                    $order = $resource['order'];
                }
                $resource = $resource['resource'];
            }

            // if resource filename begins with ! considering it as a signal that given asset can be loaded asynchronously
            if ('*' === \substr($resource, 0, 1)) {
                $result[self::TYPE_NON_BLOCKING][$order][] = \substr($resource, 1);
            } else {
                if ('!' === \substr($resource, 0, 1)) {
                    $result[self::TYPE_BLOCKING][$order][] = \substr($resource, 1);
                } else {
                    $result[self::TYPE_BLOCKING][$order][] = $resource;
                }
            }
        }

        $assets = [];
        \ksort($result[$type]);
        foreach ($result[$type] as $order => $arr) {
            $assets = \array_merge($assets, $arr);
        }

        return $assets;
    }

    public function getCssAssets(string $type): array
    {
        /** @var array<string|array{'resource': string, 'order'?: int}> $items */
        $items = $this->cssResourcesProvider->getItems();

        return $this->filterRawAssetsByType($type, $items);
    }

    public function getJavascriptAssets(string $type): array
    {
        /** @var array<string|array{'resource': string, 'order'?: int}> $items */
        $items = $this->jsResourcesProvider->getItems();

        return $this->filterRawAssetsByType($type, $items);
    }
}
