<?php

namespace Modera\DynamicallyConfigurableAppBundle;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Runtime\SymfonyRuntime;

if (\class_exists(SymfonyRuntime::class)) {
    abstract class BaseRuntime extends SymfonyRuntime
    {
    }
} else {
    abstract class BaseRuntime
    {
        public function __construct()
        {
            throw new \RuntimeException('"symfony/runtime" package required');
        }
    }
}

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class Runtime extends BaseRuntime
{
    /**
     * @param array{
     *     'env_var_name'?: string,
     *     'debug_var_name'?: string
     * } $options
     */
    public function __construct(array $options = [])
    {
        $envKey = $options['env_var_name'] ?? $options['env_var_name'] = 'APP_ENV';
        $debugKey = $options['debug_var_name'] ?? $options['debug_var_name'] = 'APP_DEBUG';

        $kernelConfig = $this->getKernelConfig();
        (new Dotenv())->populate([
            $envKey => $kernelConfig['env'],
            $debugKey => $kernelConfig['debug'] ? '1' : '0',
        ]);

        // @phpstan-ignore-next-line
        parent::__construct($options);
    }

    /**
     * @return array{'debug': bool, 'env': string}
     */
    protected function getKernelConfig(): array
    {
        return KernelConfig::read();
    }
}
