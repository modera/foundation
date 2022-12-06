<?php

namespace Modera\MjrIntegrationBundle\AssetsHandling;

/**
 * @author Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
interface AssetsProviderInterface
{
    const TYPE_BLOCKING = 'blocking';
    const TYPE_NON_BLOCKING = 'non_blocking';

    /**
     * @return string[]
     */
    public function getCssAssets(string $type): array;

    /**
     * @return string[]
     */
    public function getJavascriptAssets(string $type): array;
}
