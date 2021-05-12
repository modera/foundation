<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\ConfigBundle\Config\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
{
    /**
     * @var ConfigurationEntriesManagerInterface
     */
    private $mgr;

    /**
     * @var ValueResolverInterface|null
     */
    private $resolver;

    /**
     * @param ConfigurationEntriesManagerInterface $mgr
     * @param ValueResolverInterface|null $resolver
     */
    public function __construct(ConfigurationEntriesManagerInterface $mgr, ValueResolverInterface $resolver = null)
    {
        $this->mgr = $mgr;
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = array();

        $mjrExtJsUrl = $this->findAndResolve(Bundle::CONFIG_MJR_EXT_JS);
        if ($mjrExtJsUrl) {
            $items[] = array('order' => PHP_INT_MAX, 'resource' => $mjrExtJsUrl);
        }

        return $items;
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function findAndResolve($name)
    {
        $value = $this->mgr->findOneByNameOrDie($name)->getValue();
        if ($this->resolver) {
            $value = $this->resolver->resolve($name, $value);
        }
        return $value;
    }
}
