<?php

namespace Modera\ModuleBundle\Composer;

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
     * @return array<string, mixed>
     */
    public static function getOptions(Composer $composer): array
    {
        /** @var array{'modera-module'?: array<string, mixed>} $extra */
        // @phpstan-ignore-next-line
        $extra = $composer->getPackage()->getExtra();
        $options = \array_merge([
            'type' => 'modera-module',
            'packagist-url' => 'https://packagist.org',
        ], $extra['modera-module'] ?? []);

        return $options;
    }

    /**
     * @return string[]
     */
    public static function getRegisterBundles(Composer $composer, ?string $type = null): array
    {
        /** @var string $vendorDir */
        // @phpstan-ignore-next-line
        $vendorDir = $composer->getConfig()->get('vendor-dir');

        /** @var array{'type': string} $options */
        $options = static::getOptions($composer);

        $bundles = [];
        foreach (static::getInstalledPackages($composer, $type) as $package) {
            $bundles = \array_merge(
                $bundles,
                static::combineRegisterBundles(
                    // @phpstan-ignore-next-line
                    $package->getExtra(),
                    $options['type'],
                    // @phpstan-ignore-next-line
                    $vendorDir.DIRECTORY_SEPARATOR.$package->getName()
                )
            );
        }

        return $bundles;
    }

    /**
     * @param array<string, mixed> $extra
     *
     * @return string[]
     */
    protected static function combineRegisterBundles(array $extra, string $type, string $packageDir): array
    {
        $bundles = [];
        if (\is_array($extra[$type] ?? null)) {
            if (isset($extra[$type]['register-bundle'])) {
                if (\is_array($extra[$type]['register-bundle'])) {
                    $bundles = \array_merge($bundles, $extra[$type]['register-bundle']);
                } else {
                    $bundles[] = $extra[$type]['register-bundle'];
                }
            }

            if (isset($extra[$type]['include'])) {
                $patterns = [];
                foreach ($extra[$type]['include'] as $path) {
                    $patterns[] = $packageDir.DIRECTORY_SEPARATOR.$path;
                }

                $files = \array_map(
                    function ($files, $pattern) {
                        return $files;
                    },
                    \array_map('glob', $patterns),
                    $patterns
                );

                /** @var callable $callback */
                $callback = 'array_merge';
                foreach (\array_reduce($files, $callback, []) as $path) {
                    // @phpstan-ignore-next-line
                    $file = new JsonFile($path);
                    // @phpstan-ignore-next-line
                    $json = $file->read();
                    if (isset($json['extra'])) {
                        $bundles = \array_merge($bundles, static::combineRegisterBundles($json['extra'], $type, \dirname($path)));
                    }
                }
            }
        }

        return $bundles;
    }

    /**
     * @return CompletePackage[]
     */
    protected static function getInstalledPackages(Composer $composer, ?string $type = null): array
    {
        // @phpstan-ignore-next-line
        $repo = $composer->getRepositoryManager()->getLocalRepository();

        $packages = [];
        // @phpstan-ignore-next-line
        foreach ($repo->getPackages() as $package) {
            if ($type && false === \strpos($package->getType(), $type)) {
                continue;
            }
            if (!isset($packages[$package->getName()])
                || !\is_object($packages[$package->getName()])
                // @phpstan-ignore-next-line
                || \version_compare($packages[$package->getName()]->getVersion(), $package->getVersion(), '<')
            ) {
                $packages[$package->getName()] = $package;
            }
        }

        return $packages;
    }
}
