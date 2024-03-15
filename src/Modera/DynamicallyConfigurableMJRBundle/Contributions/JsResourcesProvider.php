<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
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
