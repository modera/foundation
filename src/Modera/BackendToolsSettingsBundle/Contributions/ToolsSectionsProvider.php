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

    private $sectionsProvider;

    /**
     * @param ContributorInterface $sectionsProvider
     */
    public function __construct(ContributorInterface $sectionsProvider)
    {
        $this->sectionsProvider = $sectionsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = array();
            if (count($this->sectionsProvider->getItems())) {
                $this->items[] = new Section(
                    T::trans('Settings'),
                    'tools.settings',
                    T::trans('Configure the current site.'),
                    '', '',
                    'modera-backend-tools-settings-icon'
                );
            }
        }

        return $this->items;
    }
}
