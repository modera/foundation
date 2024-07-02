<?php

namespace Modera\MjrIntegrationBundle\Config;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CallbackConfigMerger implements ConfigMergerInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct($callback)
    {
        if (!\is_callable($callback)) {
            throw new \InvalidArgumentException('Given $callback is not callable.');
        }

        $this->callback = $callback;
    }

    public function merge(array $existingConfig): array
    {
        return \call_user_func($this->callback, $existingConfig);
    }
}
