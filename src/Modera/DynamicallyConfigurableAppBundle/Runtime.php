<?php

namespace Modera\DynamicallyConfigurableAppBundle;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Runtime\SymfonyRuntime;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class Runtime extends SymfonyRuntime
{
    public function __construct(array $options = [])
    {
        $envKey = $options['env_var_name'] ?? $options['env_var_name'] = 'APP_ENV';
        $debugKey = $options['debug_var_name'] ?? $options['debug_var_name'] = 'APP_DEBUG';

        $kernelConfig = $this->getKernelConfig();
        (new Dotenv(false))->populate(array(
            $envKey => $kernelConfig['env'],
            $debugKey => $kernelConfig['debug'] ? '1' : '0',
        ));

        parent::__construct($options);
    }

    protected function getKernelConfig(): array
    {
        return KernelConfig::read();
    }
}
