<?php

namespace Modera\BackendConfigUtilsBundle\Controller;

use Modera\ConfigBundle\Entity\ConfigurationEntry;
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
            'entity' => ConfigurationEntry::clazz(),
            'security' => array(
                'role' => ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS,
            ),
            'hydration' => array(
                'groups' => array(
                    'list' => function (ConfigurationEntry $entry) {
                        return array(
                            'id' => $entry->getId(),
                            'name' => $entry->getName(),
                            'readableName' => $entry->getReadableName(),
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
}
