<?php

namespace Modera\DynamicallyConfigurableAppBundle;

/**
 * @copyright 2014 Modera Foundation
 */
class KernelConfig implements KernelConfigInterface
{
    protected static function getRootDir(): string
    {
        $refKernel = null;

        if (\class_exists(\App\Kernel::class)) {
            $refKernel = new \ReflectionClass(\App\Kernel::class);
        } elseif (\class_exists(\AppKernel::class)) {
            $refKernel = new \ReflectionClass(\AppKernel::class);
        }

        if (null === $refKernel || !$refKernel->getFileName()) {
            throw new \RuntimeException('Undefined project structure');
        }

        return \dirname($refKernel->getFileName());
    }

    protected static function getKernelJsonPath(): string
    {
        return static::getRootDir().\DIRECTORY_SEPARATOR.'kernel.json';
    }

    public static function write(array $mode): void
    {
        $kernelJson = \array_merge(static::read(), $mode);
        $kernelJson['_comment'] = 'This file is used to control with what configuration AppKernel should be created with.';

        \file_put_contents(static::getKernelJsonPath(), \json_encode($kernelJson, \JSON_PRETTY_PRINT));
    }

    public static function read(): array
    {
        $defaultMode = [
            'env' => 'prod',
            'debug' => false,
        ];

        $mode = @\file_get_contents(static::getKernelJsonPath());

        if (false === $mode) {
            return $defaultMode;
        } else {
            $mode = \json_decode($mode, true);
            if (\is_array($mode) && \is_string($mode['env'] ?? null) && \is_bool($mode['debug'] ?? null)) {
                return $mode;
            } else {
                return $defaultMode;
            }
        }
    }
}
