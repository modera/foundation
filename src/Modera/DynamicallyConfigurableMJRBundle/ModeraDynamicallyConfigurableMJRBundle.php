<?php

namespace Modera\DynamicallyConfigurableMJRBundle;

use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraDynamicallyConfigurableMJRBundle extends Bundle implements ExtensionPointsAwareBundleInterface
{
    public const CONFIG_TITLE = 'site_name';
    public const CONFIG_URL = 'url';
    public const CONFIG_HOME_SECTION = 'home_section';
    public const CONFIG_SKIN_CSS = 'skin_css';
    public const CONFIG_MJR_EXT_JS = 'mjr_ext_js';
    public const CONFIG_LOGO_URL = 'logo_url';

    public function getExtensionPointContributions(): array
    {
        return [
            'modera_routing.routing_resources_provider' => [
                '@ModeraDynamicallyConfigurableMJRBundle/Resources/config/routing.yml',
            ],
        ];
    }
}
