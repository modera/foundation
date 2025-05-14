<?php

namespace Modera\BackendToolsBundle\Controller;

use Modera\BackendToolsBundle\Section\Section;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2013 Modera Foundation
 */
#[AsController]
class DefaultController extends AbstractController
{
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    /**
     * @Remote
     *
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    public function getSectionsAction(array $params): array
    {
        $result = [];
        foreach ($this->getSections() as $section) {
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

    /**
     * @return Section[]
     */
    private function getSections(): array
    {
        $id = 'modera_backend_tools.sections';
        if ($this->extensionProvider->has($id)) {
            $provider = $this->extensionProvider->get($id);

            /** @var Section[] $sections */
            $sections = $provider->getItems();

            return $sections;
        }

        return [];
    }
}
