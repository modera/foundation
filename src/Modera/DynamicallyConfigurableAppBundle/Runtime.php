<?php

namespace Modera\DynamicallyConfigurableAppBundle;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Runtime\SymfonyRuntime;

/**
 * @copyright 2021 Modera Foundation
 */
class Runtime extends SymfonyRuntime
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
        if (\class_exists(Dotenv::class)) {
            (new Dotenv())->populate([
                $envKey => $kernelConfig['env'],
                $debugKey => $kernelConfig['debug'] ? '1' : '0',
            ]);
        } else {
            if (\putenv($envKey.'='.$kernelConfig['env'])) {
                $_SERVER[$envKey] = $_ENV[$envKey] = $kernelConfig['env'];
            }
            if (\putenv($debugKey.'='.($kernelConfig['debug'] ? '1' : '0'))) {
                $_SERVER[$envKey] = $_ENV[$envKey] = $kernelConfig['debug'] ? '1' : '0';
            }
        }

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
