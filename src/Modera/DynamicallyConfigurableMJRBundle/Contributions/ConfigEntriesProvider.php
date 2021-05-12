<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ConfigBundle\Config\ConfigurationEntryDefinition as CED;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigEntriesProvider implements ContributorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return [
            new CED(Bundle::CONFIG_TITLE, 'Site name', 'Modera Foundation', 'general'),
            new CED(Bundle::CONFIG_URL, 'Default URL', '', 'general'),
            new CED(Bundle::CONFIG_HOME_SECTION, 'Default section to open when user logs in to backend', '', 'general'),
            new CED(Bundle::CONFIG_SKIN_CSS, 'Skin CSS URL', '', 'general'),
            new CED(Bundle::CONFIG_MJR_EXT_JS, 'JS runtime extension URL', '', 'general'),
            new CED(Bundle::CONFIG_LOGO_URL, 'Logo URL', '', 'general'),
        ];
    }

    /**
     * @return string
     */
    public static function clazz()
    {
        return get_called_class();
    }
}
