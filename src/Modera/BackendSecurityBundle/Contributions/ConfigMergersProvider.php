<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\BackendSecurityBundle\Section\SectionInterface;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $semanticConfig;

    private ContributorInterface $sectionsProvider;

    /**
     * @param array<string, mixed> $semanticConfig
     */
    public function __construct(ContributorInterface $sectionsProvider, array $semanticConfig = [])
    {
        $this->sectionsProvider = $sectionsProvider;
        $this->semanticConfig = $semanticConfig;
    }

    public function merge(array $existingConfig): array
    {
        $existingConfig = \array_merge($existingConfig, [
            'modera_backend_security' => [
                'hideDeleteUserFunctionality' => (bool) $this->semanticConfig['hide_delete_user_functionality'],
                'sections' => [],
            ],
        ]);

        foreach ($this->sectionsProvider->getItems() as $section) {
            if ($section instanceof SectionInterface) {
                $existingConfig['modera_backend_security']['sections'][] = [
                    'sectionConfig' => [
                        'name' => $section->getId(),
                        'uiClass' => $section->getUiClass(),
                    ],
                    'menuConfig' => [
                        'itemId' => $section->getId(),
                        'text' => $section->getTitle(),
                        'glyph' => $section->getGlyphIcon(),
                    ],
                ];
            }
        }

        return $existingConfig;
    }

    public function getItems(): array
    {
        return [$this];
    }
}
