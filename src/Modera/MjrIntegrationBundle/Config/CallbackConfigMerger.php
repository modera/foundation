<?php

namespace Modera\MjrIntegrationBundle\Config;

/**
 * @copyright 2014 Modera Foundation
 */
class CallbackConfigMerger implements ConfigMergerInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function merge(array $existingConfig): array
    {
        return \call_user_func($this->callback, $existingConfig);
    }
}
