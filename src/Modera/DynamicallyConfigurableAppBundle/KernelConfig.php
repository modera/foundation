<?php

namespace Modera\DynamicallyConfigurableAppBundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class KernelConfig
{
    /**
     * @return array
     */
    public static function read()
    {
        $defaultMode = array(
            'env' => 'prod',
            'debug' => false,
        );

        $reflKernel = new \ReflectionClass('AppKernel');

        $mode = file_get_contents(self::getKernelJsonPath());

        if (false == $mode) {
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

    public static function getKernelJsonPath()
    {
        $reflKernel = new \ReflectionClass('AppKernel');

        $appDir = dirname($reflKernel->getFileName());

        $isShared = getenv('MODERA_SD');
        $sharedDeploymentName = getenv('MODERA_SD_NAME');

        if ($isShared && $sharedDeploymentName) {
            return $appDir.'/../deployments/'.$sharedDeploymentName.'/kernel.json';
        } else {
            return $appDir.'/kernel.json';
        }
    }
}
