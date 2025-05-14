<?php

namespace Modera\DynamicallyConfigurableAppBundle\Contributions;

use Modera\ConfigBundle\Config\BooleanHandler;
use Modera\ConfigBundle\Config\ConfigurationEntryDefinition as CED;
use Modera\ConfigBundle\Config\DictionaryHandler;
use Modera\DynamicallyConfigurableAppBundle\ModeraDynamicallyConfigurableAppBundle as Bundle;
use Modera\DynamicallyConfigurableAppBundle\ValueHandling\KernelConfigWriter;
use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Translation\T;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_config.config_entries')]
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
            'handler' => BooleanHandler::class,
            'update_handler' => KernelConfigWriter::class,
            'true_text' => $yes,
            'false_text' => $no,
        ];

        $kernelDebugClient = [
            'xtype' => 'combo',
            'store' => [['prod', 'yes'], ['dev', 'no']],
        ];

        $kernelEnvServer = [
            'handler' => DictionaryHandler::class,
            'update_handler' => KernelConfigWriter::class,
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
