<?php

namespace Modera\MjrIntegrationBundle\AssetsHandling;

/**
 * @copyright 2022 Modera Foundation
 */
interface AssetsProviderInterface
{
    public const TYPE_BLOCKING = 'blocking';
    public const TYPE_NON_BLOCKING = 'non_blocking';

    /**
     * @return string[]
     */
    public function getCssAssets(string $type): array;

    /**
     * @return string[]
     */
    public function getJavascriptAssets(string $type): array;
}
