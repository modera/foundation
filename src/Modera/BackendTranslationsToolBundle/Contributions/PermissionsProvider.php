<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Model\Permission;

/**
 * @copyright 2017 Modera Foundation
 */
#[AsContributorFor('modera_security.permissions')]
class PermissionsProvider implements ContributorInterface
{
    /**
     * @var Permission[]
     */
    private ?array $items = null;

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [
                new Permission(
                    T::trans('Access Translations Tool'),
                    ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION,
                    'administration',
                ),
            ];
        }

        return $this->items;
    }
}
