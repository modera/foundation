<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\BackendToolsBundle\Section\Section;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Contributes a section to Backend/Tools.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ToolsSectionsProvider implements ContributorInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * @var Section[]
     */
    private ?array $items = null;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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
                    'modera-backend-translations-tool-tools-icon'
                );
            }
        }

        return $this->items;
    }
}
