<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
{
    private ConfigurationEntriesManagerInterface $mgr;

    private ?ValueResolverInterface $resolver;

    public function __construct(ConfigurationEntriesManagerInterface $mgr, ?ValueResolverInterface $resolver = null)
    {
        $this->mgr = $mgr;
        $this->resolver = $resolver;
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

    /**
     * @return mixed Mixed value
     */
    private function findAndResolve(string $name)
    {
        $value = $this->mgr->findOneByNameOrDie($name)->getValue();
        if ($this->resolver) {
            $value = $this->resolver->resolve($name, $value);
        }

        return $value;
    }
}
