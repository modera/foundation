<?php

namespace Modera\MjrIntegrationBundle\Config;

/**
 * Implementations of this interface are responsible for contributing configuration which later will be exposed
 * to JavaScript runtime.
 *
 * @copyright 2013 Modera Foundation
 */
interface ConfigMergerInterface
{
    /**
     * @param array<mixed> $existingConfig
     *
     * @return array<mixed>
     */
    public function merge(array $existingConfig): array;
}
