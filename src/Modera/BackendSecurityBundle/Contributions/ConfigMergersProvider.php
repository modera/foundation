<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\BackendSecurityBundle\Section\SectionInterface;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @internal
 *
 * @copyright 2017 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.config.config_mergers')]
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    /**
     * @param array<string, mixed> $semanticConfig
     */
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
        private readonly array $semanticConfig = [],
    ) {
    }

    public function merge(array $existingConfig): array
    {
        $existingConfig = \array_merge($existingConfig, [
            'modera_backend_security' => [
                'hideDeleteUserFunctionality' => (bool) $this->semanticConfig['hide_delete_user_functionality'],
                'sections' => [],
            ],
        ]);

        foreach ($this->extensionProvider->get('modera_backend_security.sections')->getItems() as $section) {
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
