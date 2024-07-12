<?php

namespace Modera\MjrIntegrationBundle\Config;

/**
 * Implementation of this interface will be used by the bundle to provide low-level user-specific configuration for the
 * MJR.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
interface MainConfigInterface
{
    /**
     * Method must return a site name that will be displayed in a header.
     */
    public function getTitle(): ?string;

    /**
     * Method must return a URL that user will be redirected if he clicks on "siteTitle".
     */
    public function getUrl(): ?string;

    /**
     * A section "alias" that should be loaded by default when user opens backend.
     */
    public function getHomeSection(): ?string;
}
