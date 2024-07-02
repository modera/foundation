<?php

namespace Modera\BackendToolsActivityLogBundle;

use Modera\ExpanderBundle\Contributing\ExtensionPointsAwareBundleInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\MjrIntegrationBundle\Sections\Section as MJRSection;
use Modera\SecurityBundle\Model\Permission;
use Modera\SecurityBundle\Model\PermissionCategory;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraBackendToolsActivityLogBundle extends Bundle implements ExtensionPointsAwareBundleInterface
{
    public const ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION = 'ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION';

    public function getExtensionPointContributions(): array
    {
        return [
            'modera_mjr_integration.css_resources_provider' => [
                '/bundles/moderabackendtoolsactivitylog/css/styles.css',
            ],
            'modera_mjr_integration.sections_provider' => [
                new MJRSection('tools.activitylog', 'Modera.backend.tools.activitylog.runtime.Section', [
                    MJRSection::META_NAMESPACE => 'Modera.backend.tools.activitylog',
                    MJRSection::META_NAMESPACE_PATH => '/bundles/moderabackendtoolsactivitylog/js',
                ]),
            ],
            'modera_security.permission_categories_provider' => [
                new PermissionCategory(
                    T::trans('Administration'),
                    'administration'
                ),
            ],
            'modera_security.permissions_provider' => [
                new Permission(
                    T::trans('Access Activity Log'),
                    self::ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION,
                    'administration'
                ),
            ],
        ];
    }
}
