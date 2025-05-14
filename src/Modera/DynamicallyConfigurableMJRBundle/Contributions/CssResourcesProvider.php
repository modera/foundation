<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2019 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.css_resources')]
class CssResourcesProvider implements ContributorInterface
{
    public function __construct(
        private readonly ConfigurationEntriesManagerInterface $mgr,
        private readonly ?ValueResolverInterface $resolver = null,
    ) {
    }

    public function getItems(): array
    {
        $items = [];

        /** @var ?string $logoUrl */
        $logoUrl = $this->findAndResolve(Bundle::CONFIG_LOGO_URL);
        if ($logoUrl) {
            $items[] = ['order' => PHP_INT_MAX, 'resource' => '/bundles/moderadynamicallyconfigurablemjr/css/logo.css'];
            $items[] = ['order' => PHP_INT_MAX, 'resource' => '/logo.css'];
        }

        /** @var ?string $skinCssUrl */
        $skinCssUrl = $this->findAndResolve(Bundle::CONFIG_SKIN_CSS);
        if ($skinCssUrl) {
            $needle = '_TIMESTAMP_';
            if (false !== \mb_strpos($skinCssUrl, $needle)) {
                $skinCssUrl = \str_replace($needle, (string) \time(), $skinCssUrl);
            }
            $items[] = ['order' => PHP_INT_MAX, 'resource' => $skinCssUrl];
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
