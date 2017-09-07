<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Model\Permission;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class PermissionsProvider implements ContributorInterface
{
    private $items;

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = [
                new Permission(
                    T::trans('Access Translations Tool'),
                    ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION,
                    'administration'
                ),
            ];
        }

        return $this->items;
    }
}
