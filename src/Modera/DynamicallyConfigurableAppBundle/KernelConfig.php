<?php

namespace Modera\DynamicallyConfigurableAppBundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class KernelConfig implements KernelConfigInterface
{
    protected static function getRootDir(): string
    {
        $refKernel = null;
        try {
            $refKernel = new \ReflectionClass('App\Kernel');
        } catch (\Exception $e) {
            // TODO: BC
            $refKernel = new \ReflectionClass('AppKernel');
        } catch (\Exception $e) {}

        if (null === $refKernel) {
            throw new \RuntimeException('Undefined project structure');
        }

        return \dirname($refKernel->getFileName());
    }

    protected static function getKernelJsonPath(): string
    {
        return static::getRootDir() . DIRECTORY_SEPARATOR . 'kernel.json';
    }

    /**
     * {@inheritdoc}
     */
    public static function write(array $mode)
    {
        $kernelJson = array_merge(static::read(), $mode);
        $kernelJson['_comment'] = 'This file is used to control with what configuration AppKernel should be created with.';

        file_put_contents(static::getKernelJsonPath(), json_encode($kernelJson, \JSON_PRETTY_PRINT));
    }

    /**
     * {@inheritdoc}
     */
    public static function read(): array
    {
        $defaultMode = array(
            'env'   => 'prod',
            'debug' => false,
        );

        $mode = @file_get_contents(static::getKernelJsonPath());

        if (false === $mode) {
            return $defaultMode;
        } else {
            $mode = json_decode($mode, true);
            if (is_array($mode) && isset($mode['env']) && isset($mode['debug'])) {
                return $mode;
            } else {
                return $defaultMode;
            }
        }
    }
}
