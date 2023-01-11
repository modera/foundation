<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
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

        $logoUrl = $this->findAndResolve(Bundle::CONFIG_LOGO_URL);
        if ($logoUrl) {
            $items[] = array('order' => PHP_INT_MAX, 'resource' => '/bundles/moderadynamicallyconfigurablemjr/css/logo.css');
            $items[] = array('order' => PHP_INT_MAX, 'resource' => '/logo.css');
        }

        $skinCssUrl = $this->findAndResolve(Bundle::CONFIG_SKIN_CSS);
        if ($skinCssUrl) {
            $needle = '_TIMESTAMP_';
            if (\mb_strpos($skinCssUrl, $needle) !== false) {
                $skinCssUrl = \str_replace($needle, \time(), $skinCssUrl);
            }
            $items[] = array('order' => PHP_INT_MAX, 'resource' => $skinCssUrl);
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
