<?php

namespace  Modera\ModuleBundle\Composer;

use Composer\Composer;
use Composer\Json\JsonFile;
use Composer\Package\CompletePackage;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class Helper
{
    /**
     * @param Composer $composer
     * @return array
     */
    public static function getOptions(Composer $composer)
    {
        $extra = $composer->getPackage()->getExtra();
        $options = array_merge(array(
            'type'          => 'modera-module',
            'packagist-url' => 'https://packagist.org',
        ), isset($extra['modera-module']) ? $extra['modera-module'] : array());

        return $options;
    }

    /**
     * @param Composer $composer
     * @param string|null $type
     * @return array
     */
    public static function getRegisterBundles(Composer $composer, $type = null)
    {
        $bundles = array();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $options = static::getOptions($composer);

        foreach (static::getInstalledPackages($composer, $type) as $package) {
            $bundles = array_merge(
                $bundles,
                static::combineRegisterBundles(
                    $package->getExtra(), $options['type'], $vendorDir . DIRECTORY_SEPARATOR . $package->getName()
                )
            );
        }

        return $bundles;
    }

    /**
     * @param array $extra
     * @param string $type
     * @param string $packageDir
     * @return array
     */
    protected static function combineRegisterBundles(array $extra, $type, $packageDir)
    {
        $bundles = array();
        if (isset($extra[$type])) {
            if (isset($extra[$type]['register-bundle'])) {
                if (is_array($extra[$type]['register-bundle'])) {
                    $bundles = array_merge($bundles, $extra[$type]['register-bundle']);
                } else {
                    $bundles[] = $extra[$type]['register-bundle'];
                }
            }

            if (isset($extra[$type]['include'])) {
                $patterns = array();
                foreach ($extra[$type]['include'] as $path) {
                    $patterns[] = $packageDir . DIRECTORY_SEPARATOR . $path;
                }

                $files = array_map(
                    function ($files, $pattern) {
                        return $files;
                    },
                    array_map('glob', $patterns),
                    $patterns
                );

                foreach (array_reduce($files, 'array_merge', array()) as $path) {
                    $file = new JsonFile($path);
                    $json = $file->read();
                    if (isset($json['extra'])) {
                        $bundles = array_merge($bundles, static::combineRegisterBundles($json['extra'], $type, dirname($path)));
                    }
                }
            }
        }

        return $bundles;
    }

    /**
     * @param Composer $composer
     * @param null|string $type
     * @return CompletePackage[]
     */
    protected static function getInstalledPackages(Composer $composer, $type = null)
    {
        $packages = array();
        $repo = $composer->getRepositoryManager()->getLocalRepository();
        foreach ($repo->getPackages() as $package) {
            if ($type && strpos($package->getType(), $type) === false) {
                continue;
            }
            if (!isset($packages[$package->getName()])
                || !is_object($packages[$package->getName()])
                || version_compare($packages[$package->getName()]->getVersion(), $package->getVersion(), '<')
            ) {
                $packages[$package->getName()] = $package;
            }
        }

        return $packages;
    }
}
