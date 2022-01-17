<?php

namespace Modera\BackendConfigUtilsBundle\Controller;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\ConfigBundle\Entity\ConfigurationEntry;
use Modera\ConfigBundle\Config\ConfigurationEntryInterface;
use Modera\ConfigBundle\Config\ConfigurationEntryDefinition;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class DefaultController extends AbstractCrudController
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return array(
            'entity' => ConfigurationEntry::class,
            'security' => array(
                'role' => ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS,
            ),
            'hydration' => array(
                'groups' => array(
                    'list' => function (ConfigurationEntry $entry) {
                        $readableName = $entry->getReadableName();
                        if ($entryDef = $this->getEntryDef($entry)) {
                            $readableName = $entryDef->getReadableName();
                        }

                        return array(
                            'id' => $entry->getId(),
                            'name' => $entry->getName(),
                            'readableName' => $readableName,
                            'readableValue' => $entry->getReadableValue(),
                            'value' => $entry->getValue(),
                            'isReadOnly' => $entry->isReadOnly(),
                            'editorConfig' => $entry->getClientHandlerConfig(),
                        );
                    },
                ),
                'profiles' => ['list'],
            ),
            'map_data_on_update' => function (array $params, ConfigurationEntry $entry) {
                if ($entry->isReadOnly() || !$entry->isExposed()) {
                    return;
                }

                if (isset($params['value'])) {
                    $entry->setValue($params['value']);
                }
            },
        );
    }

    /**
     * @param ConfigurationEntry $entity
     * @return null|ConfigurationEntryDefinition
     */
    private function getEntryDef(ConfigurationEntry $entity)
    {
        /* @var ContributorInterface $provider */
        $provider = $this->get('modera_config.config_entries_provider');
        foreach ($provider->getItems() as $entryDef) {
            /* @var ConfigurationEntryInterface $entryDef */
            if ($entity->getName() == $entryDef->getName()) {
                return $entryDef;
            }
        }
        return null;
    }
}
