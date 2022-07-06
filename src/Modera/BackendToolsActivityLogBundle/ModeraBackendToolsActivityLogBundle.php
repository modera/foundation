<?php

namespace Modera\BackendToolsActivityLogBundle;

use Modera\FoundationBundle\Translation\T;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Modera\MjrIntegrationBundle\Sections\Section as MJRSection;
use Sli\ExpanderBundle\Contributing\ExtensionPointsAwareBundleInterface;
use Modera\SecurityBundle\Model\PermissionCategory;
use Modera\SecurityBundle\Model\Permission;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendToolsActivityLogBundle extends Bundle implements ExtensionPointsAwareBundleInterface
{
    const ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION = 'ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION';

    /**
     * {@inheritdoc}
     */
    public function getExtensionPointContributions(): array
    {
        return array(
            'modera_mjr_integration.css_resources_provider' => array(
                '/bundles/moderabackendtoolsactivitylog/css/styles.css',
            ),
            'modera_mjr_integration.sections_provider' => array(
                new MJRSection('tools.activitylog', 'Modera.backend.tools.activitylog.runtime.Section', array(
                    MJRSection::META_NAMESPACE => 'Modera.backend.tools.activitylog',
                    MJRSection::META_NAMESPACE_PATH => '/bundles/moderabackendtoolsactivitylog/js',
                )),
            ),
            'modera_security.permission_categories_provider' => array(
                new PermissionCategory(
                    T::trans('Administration'),
                    'administration'
                ),
            ),
            'modera_security.permissions_provider' => array(
                new Permission(
                    T::trans('Access Activity Log'),
                    self::ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION,
                    'administration'
                ),
            ),
        );
    }
}
