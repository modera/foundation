<?php

namespace Modera\BackendConfigUtilsBundle\Controller;

use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\ConfigBundle\Config\ConfigurationEntryDefinition;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class DefaultController extends AbstractCrudController
{
    public function __construct(
        private readonly ExtensionProvider $extensionProvider,
    ) {
    }

    public function getConfig(): array
    {
        return [
            'entity' => ConfigurationEntry::class,
            'security' => [
                'role' => ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS,
            ],
            'hydration' => [
                'groups' => [
                    'list' => function (ConfigurationEntry $entry) {
                        $readableName = $entry->getReadableName();
                        if ($entryDef = $this->getEntryDef($entry)) {
                            $readableName = $entryDef->getReadableName();
                        }

                        return [
                            'id' => $entry->getId(),
                            'name' => $entry->getName(),
                            'readableName' => $readableName,
                            'readableValue' => $entry->getReadableValue(),
                            'value' => $entry->getValue(),
                            'isReadOnly' => $entry->isReadOnly(),
                            'editorConfig' => $entry->getClientHandlerConfig(),
                        ];
                    },
                ],
                'profiles' => ['list'],
            ],
            'map_data_on_update' => function (array $params, ConfigurationEntry $entry) {
                if ($entry->isReadOnly() || !$entry->isExposed()) {
                    return;
                }

                if (isset($params['value'])) {
                    $entry->setValue($params['value']);
                }
            },
        ];
    }

    private function getEntryDef(ConfigurationEntry $entity): ?ConfigurationEntryDefinition
    {
        $id = 'modera_config.config_entries';
        if ($this->extensionProvider->has($id)) {
            $provider = $this->extensionProvider->get($id);
            foreach ($provider->getItems() as $entryDef) {
                /** @var ConfigurationEntryDefinition $entryDef */
                if ($entity->getName() == $entryDef->getName()) {
                    return $entryDef;
                }
            }
        }

        return null;
    }
}
