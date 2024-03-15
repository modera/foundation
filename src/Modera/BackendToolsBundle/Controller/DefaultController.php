<?php

namespace Modera\BackendToolsBundle\Controller;

use Modera\BackendToolsBundle\Section\Section;
use Modera\DirectBundle\Annotation\Remote;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Controller\AbstractBaseController;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class DefaultController extends AbstractBaseController
{
    /**
     * @Remote
     *
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    public function getSectionsAction(array $params): array
    {
        /** @var ContributorInterface $sectionsProvider */
        $sectionsProvider = $this->container->get('modera_backend_tools.sections_provider');

        $result = [];
        /** @var Section $section */
        foreach ($sectionsProvider->getItems() as $section) {
            $result[] = [
                'name' => $section->getName(),
                'glyph' => $section->getGlyph(),
                'iconSrc' => $section->getIconSrc(),
                'iconCls' => $section->getIconClass(),
                'description' => $section->getDescription(),
                'section' => $section->getSection(),
                'activationParams' => $section->getSectionActivationParams(),
            ];
        }

        return $result;
    }
}
