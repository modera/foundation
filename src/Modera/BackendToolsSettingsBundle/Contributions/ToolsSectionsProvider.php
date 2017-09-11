<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\BackendToolsBundle\Section\Section;
use Modera\FoundationBundle\Translation\T;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * Contributes a section to Backend/Tools.
 *
 * @internal Since 2.56.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ToolsSectionsProvider implements ContributorInterface
{
    private $items;

    /**
     * @param ContributorInterface $sectionsProvider
     */
    public function __construct(ContributorInterface $sectionsProvider)
    {
        $this->items = array();
        if (count($sectionsProvider->getItems())) {
            $this->items[] = new Section(
                T::trans('Settings'),
                'tools.settings',
                T::trans('Configure the current site.'),
                '', '',
                'modera-backend-tools-settings-icon'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->items;
    }
}
