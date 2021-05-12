<?php

namespace Modera\DynamicallyConfigurableMJRBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Sli\ExpanderBundle\Contributing\ExtensionPointsAwareBundleInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraDynamicallyConfigurableMJRBundle extends Bundle implements ExtensionPointsAwareBundleInterface
{
    const CONFIG_TITLE = 'site_name';
    const CONFIG_URL = 'url';
    const CONFIG_HOME_SECTION = 'home_section';
    const CONFIG_SKIN_CSS = 'skin_css';
    const CONFIG_MJR_EXT_JS = 'mjr_ext_js';
    const CONFIG_LOGO_URL = 'logo_url';

    /**
     * {@inheritdoc}
     */
    public function getExtensionPointContributions()
    {
        return array(
            'modera_routing.routing_resources_provider' => array(
                '@ModeraDynamicallyConfigurableMJRBundle/Resources/config/routing.yml',
            ),
        );
    }
}
