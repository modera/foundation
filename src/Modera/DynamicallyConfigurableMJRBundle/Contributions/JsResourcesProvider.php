<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2021 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.js_resources')]
class JsResourcesProvider implements ContributorInterface
{
    public function __construct(
        private readonly ConfigurationEntriesManagerInterface $mgr,
        private readonly ?ValueResolverInterface $resolver = null,
    ) {
    }

    public function getItems(): array
    {
        $items = [];

        /** @var ?string $mjrExtJsUrl */
        $mjrExtJsUrl = $this->findAndResolve(Bundle::CONFIG_MJR_EXT_JS);
        if ($mjrExtJsUrl) {
            $needle = '_TIMESTAMP_';
            if (false !== \mb_strpos($mjrExtJsUrl, $needle)) {
                $mjrExtJsUrl = \str_replace($needle, (string) \time(), $mjrExtJsUrl);
            }
            $items[] = ['order' => PHP_INT_MAX, 'resource' => $mjrExtJsUrl];
        }

        return $items;
    }

    private function findAndResolve(string $name): mixed
    {
        $value = $this->mgr->findOneByNameOrDie($name)->getValue();
        if ($this->resolver) {
            $value = $this->resolver->resolve($name, $value);
        }

        return $value;
    }
}
