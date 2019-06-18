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
        $skinCssUrl = $this->mgr->findOneByNameOrDie(Bundle::CONFIG_SKIN_CSS)->getValue();
        if ($skinCssUrl) {
            return array(
                array('order' => PHP_INT_MAX, 'resource' => $skinCssUrl),
            );
        }
        return array();
    }
}
