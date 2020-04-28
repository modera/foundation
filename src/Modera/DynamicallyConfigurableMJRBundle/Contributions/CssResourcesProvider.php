<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\ConfigBundle\Config\ConfigurationEntriesManagerInterface;
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
     * @param ConfigurationEntriesManagerInterface $mgr
     */
    public function __construct(ConfigurationEntriesManagerInterface $mgr)
    {
        $this->mgr = $mgr;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = array();

        $logoUrl = $this->mgr->findOneByNameOrDie(Bundle::CONFIG_LOGO_URL)->getValue();
        if ($logoUrl) {
            $items[] = array('order' => PHP_INT_MAX, 'resource' => '/bundles/moderadynamicallyconfigurablemjr/css/logo.css');
            $items[] = array('order' => PHP_INT_MAX, 'resource' => '/logo.css');
        }

        $skinCssUrl = $this->mgr->findOneByNameOrDie(Bundle::CONFIG_SKIN_CSS)->getValue();
        if ($skinCssUrl) {
            $items[] = array('order' => PHP_INT_MAX, 'resource' => $skinCssUrl);
        }

        return $items;
    }
}
