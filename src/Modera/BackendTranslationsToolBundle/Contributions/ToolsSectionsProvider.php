<?php

namespace Modera\BackendTranslationsToolBundle\Contributions;

use Modera\FoundationBundle\Translation\T;
use Modera\BackendToolsBundle\Section\Section;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Contributes a section to Backend/Tools.
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ToolsSectionsProvider implements ContributorInterface
{
    private $authorizationChecker;

    private $items;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = array();

            if ($this->authorizationChecker->isGranted(ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION)) {
                $this->items[] = new Section(
                    T::trans('Translations'),
                    'tools.translations',
                    T::trans('A tool set for translating content from different sources.'),
                    '', '',
                    'modera-backend-translations-tool-tools-icon'
                );
            }
        }

        return $this->items;
    }
}
