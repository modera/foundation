<?php

namespace Modera\DynamicallyConfigurableAppBundle\Contributions;

use Modera\ConfigBundle\Config\ConfigurationEntryDefinition as CED;
use Modera\DynamicallyConfigurableAppBundle\ModeraDynamicallyConfigurableAppBundle as Bundle;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigEntriesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        // "client" configuration configs are not that much important when standard foundation is used because
        // "general" category relies on "Modera.backend.dcmjr.view.GeneralSettingsPanel" to display
        // and edit configuration properties which defines all required configuration right in JS file

        $yes = T::trans('yes');
        $no = T::trans('no');

        $kernelDebugServer = [
            'handler' => 'modera_config.boolean_handler',
            'update_handler' => 'modera_dynamically_configurable_app.value_handling.kernel_config_writer',
            'true_text' => $yes,
            'false_text' => $no,
        ];

        $kernelDebugClient = [
            'xtype' => 'combo',
            'store' => [['prod', 'yes'], ['dev', 'no']],
        ];

        $kernelEnvServer = [
            'handler' => 'modera_config.dictionary_handler',
            'update_handler' => 'modera_dynamically_configurable_app.value_handling.kernel_config_writer',
            'dictionary' => [
                'prod' => $yes,
                'dev' => $no,
            ],
        ];

        $kernelEnvClient = [
            'xtype' => 'combo',
            'store' => [[true, 'yes'], [false, 'no']],
        ];

        return [
            new CED(Bundle::CONFIG_KERNEL_ENV, T::trans('Production mode'), 'prod', 'general', $kernelEnvServer, $kernelEnvClient),
            new CED(Bundle::CONFIG_KERNEL_DEBUG, T::trans('Debug mode'), false, 'general', $kernelDebugServer, $kernelDebugClient),
        ];
    }
}
