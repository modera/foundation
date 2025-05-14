<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\BackendToolsBundle\Section\Section;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Contributes a section to Backend/Tools.
 *
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_backend_tools.sections')]
class ToolsSectionsProvider implements ContributorInterface
{
    /**
     * @var Section[]
     */
    private ?array $items = null;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [];

            if ($this->authorizationChecker->isGranted(ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION)) {
                $this->items[] = new Section(
                    T::trans('Translations'),
                    'tools.translations',
                    T::trans('A tool set for translating content from different sources.'),
                    '',
                    '',
                    'modera-backend-translations-tool-tools-icon',
                );
            }
        }

        return $this->items;
    }
}
