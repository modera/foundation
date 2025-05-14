<?php

namespace Modera\RoutingBundle\Routing;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Collects dynamically contributed routing resources.
 *
 * @copyright 2013 Modera Foundation
 */
class Loader implements LoaderInterface
{
    private bool $isLoaded = false;

    protected LoaderResolverInterface $resolver;

    public function __construct(
        private readonly ContributorInterface $resourcesProvider,
        private readonly LoaderInterface $rootLoader,
    ) {
    }

    public function load(mixed $resource, ?string $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "modera_routing" loader twice');
        }

        $resources = [];
        $items = $this->resourcesProvider->getItems();
        foreach ($items as $index => $resource) {
            if (!\is_array($resource)) {
                $resource = [
                    'resource' => $resource,
                ];
            }
            $resource = \array_merge(['order' => 0, 'type' => null], $resource);
            $resource['index'] = $index;
            $resources[] = $resource;
        }

        \usort($resources, function ($a, $b) {
            if ($a['order'] == $b['order']) {
                return ($a['index'] < $b['index']) ? -1 : 1;
            }

            return ($a['order'] < $b['order']) ? -1 : 1;
        });

        $collection = new RouteCollection();
        foreach ($resources as $item) {
            /** @var array{'resource': mixed,'type': string} $item */
            /** @var RouteCollection $rootCollection */
            $rootCollection = $this->rootLoader->load($item['resource'], $item['type']);
            $collection->addCollection($rootCollection);
        }

        $this->isLoaded = true;

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'modera_routing' === $type;
    }

    public function getResolver(): LoaderResolverInterface
    {
        return $this->resolver;
    }

    public function setResolver(LoaderResolverInterface $resolver): void
    {
        $this->resolver = $resolver;
    }

    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }
}
