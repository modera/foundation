<?php

namespace Modera\ModuleBundle\Composer;

use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\Event as BaseEvent;
use Composer\Installer\PackageEvent;
use Composer\Json\JsonFile;
use Composer\Script\Event;
use Modera\ModuleBundle\Composer\Script\AliasPackageEvent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2013 Modera Foundation
 */
class ScriptHandler extends AbstractScriptHandler
{
    public static function blank(Event $event): void
    {
        // do nothing
    }

    public static function enableMaintenance(Event $event): void
    {
        echo '*** Enable maintenance'.PHP_EOL;

        try {
            if (static::setMaintenance($event, true)) {
                static::clearCache($event);
            }
        } catch (\RuntimeException $e) {
            echo $e->getMessage().PHP_EOL;
        }
    }

    public static function disableMaintenance(Event $event): void
    {
        echo '*** Disable maintenance'.PHP_EOL;

        try {
            if (static::setMaintenance($event, false)) {
                static::clearCache($event);
            }
        } catch (\RuntimeException $e) {
            echo $e->getMessage().PHP_EOL;
        }
    }

    public static function packageEventDispatcher(PackageEvent $event): void
    {
        // @phpstan-ignore-next-line
        static::baseEventDispatcher($event);
    }

    public static function eventDispatcher(Event $event): void
    {
        // @phpstan-ignore-next-line
        static::baseEventDispatcher($event);
    }

    public static function registerBundles(Event $event): void
    {
        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'register bundles');
        if (null === $consoleDir) {
            return;
        }

        /** @var string $appDir */
        $appDir = $options['symfony-app-dir'];
        if (!static::hasDirectory($event, 'symfony-app-dir', $appDir)) {
            return;
        }

        $bundlesFile = 'AppModuleBundles.php';
        // @phpstan-ignore-next-line
        $bundles = Helper::getRegisterBundles($event->getComposer());

        static::createRegisterBundlesFile($bundles, $appDir.'/'.$bundlesFile);
    }

    /**
     * Clears the Symfony cache.
     */
    public static function clearCache(Event $event): void
    {
        /** @var array{'process-timeout': int} $options */
        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'clear cache');
        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'cache:clear --no-warmup --quiet', $options['process-timeout']);
    }

    /**
     * Executes the SQL needed to update the database schema to match the current mapping metadata.
     */
    public static function doctrineSchemaUpdate(Event $event): void
    {
        if ($scriptHandler = static::getScriptHandler($event, __FUNCTION__)) {
            // @phpstan-ignore-next-line
            $scriptHandler($event);

            return;
        }

        /** @var array{'process-timeout': int} $options */
        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'update doctrine schema');
        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'doctrine:schema:update --force', $options['process-timeout']);
    }

    /**
     * Creates the configured databases and executes the SQL needed to update the database schema, if database not created.
     */
    public static function initDatabase(Event $event): void
    {
        /** @var array{'process-timeout': int} $options */
        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'init DB');
        if (null === $consoleDir) {
            return;
        }

        $ignoreSchemaUpdate = false;
        try {
            static::executeCommand($event, $consoleDir, 'doctrine:database:create --quiet', $options['process-timeout']);
        } catch (\RuntimeException $e) {
            // The command throws an exception if database already exists, so here we are supressing it
            $ignoreSchemaUpdate = true;
        }

        if (!$ignoreSchemaUpdate) {
            try {
                static::doctrineSchemaUpdate($event);
            } catch (\Exception $e) {
                echo 'Error during database initialization: '.$e->getMessage().PHP_EOL;
            }
        }
    }

    protected static function setMaintenance(Event $event, bool $value): bool
    {
        $options = static::getOptions($event);

        $path = null;
        if (\is_array($options['modera-module'] ?? null) && \is_string($options['modera-module']['maintenance-file'])) {
            $path = $options['modera-module']['maintenance-file'];
        } elseif (\is_array($options['incenteev-parameters'] ?? null) && \is_string($options['incenteev-parameters']['file'])) {
            $path = $options['incenteev-parameters']['file'];
        }

        if ($path) {
            if (\file_exists($path)) {
                $data = Yaml::parse(\file_get_contents($path) ?: 'parameters:');
            } else {
                $data = [
                    'parameters' => [],
                ];
            }
            /** @var array{'parameters': array<string, mixed>} $data */
            $data = $data;

            $data['parameters']['maintenance'] = $value;

            \file_put_contents($path, Yaml::dump($data));

            return true;
        }

        return false;
    }

    /**
     * @param string[] $bundles
     */
    protected static function createRegisterBundlesFile(array $bundles, string $outputFile): void
    {
        $data = ['<?php return ['];
        foreach ($bundles as $bundleClassName) {
            $data[] = '    new '.$bundleClassName.'(),';
        }
        $data[] = '];';

        $fs = new Filesystem();
        $fs->dumpFile($outputFile, \implode(PHP_EOL, $data).PHP_EOL);

        if (!$fs->exists($outputFile)) {
            throw new \RuntimeException(\sprintf('The "%s" file must be created.', $outputFile));
        }
    }

    /**
     * @param array<string, mixed> $extra
     *
     * @return array<string, string[]>
     */
    protected static function combineScripts(array $extra, string $type, string $packageDir): array
    {
        $scripts = [];
        if (\is_array($extra[$type] ?? null)) {
            if (isset($extra[$type]['scripts'])) {
                if (\is_array($extra[$type]['scripts'])) {
                    foreach ($extra[$type]['scripts'] as $event => $handler) {
                        if (!isset($scripts[$event])) {
                            $scripts[$event] = [];
                        }

                        if (!\is_array($handler)) {
                            $handler = [$handler];
                        }

                        $scripts[$event] = \array_merge($scripts[$event], $handler);
                    }
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
                        foreach (static::combineScripts($json['extra'], $type, \dirname($path)) as $event => $handler) {
                            if (!isset($scripts[$event])) {
                                $scripts[$event] = [];
                            }

                            if (!\is_array($handler)) {
                                $handler = [$handler];
                            }

                            $scripts[$event] = \array_merge($scripts[$event], $handler);
                        }
                    }
                }
            }
        }

        return $scripts;
    }

    protected static function baseEventDispatcher(BaseEvent $event): void
    {
        static $_scripts = [];

        // @phpstan-ignore-next-line
        if ($event instanceof PackageEvent) {
            $event = new AliasPackageEvent($event);
        }

        if ($event instanceof AliasPackageEvent) {
            // @phpstan-ignore-next-line
            $operation = $event->getAliasOf()->getOperation();
            // @phpstan-ignore-next-line
            if ($operation instanceof UpdateOperation) {
                // @phpstan-ignore-next-line
                $package = $operation->getTargetPackage();
            } else {
                // @phpstan-ignore-next-line
                $package = $operation->getPackage();
            }

            /** @var array{'type': string} $options */
            // @phpstan-ignore-next-line
            $options = Helper::getOptions($event->getComposer());
            if ($package->getType() !== $options['type']) {
                // return;
            }

            $extra = $package->getExtra();
            $delayedEvents = ['post-package-install', 'post-package-update'];

            if (\is_array($extra) && isset($extra[$options['type']])) {
                // @phpstan-ignore-next-line
                $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
                $extraScripts = static::combineScripts(
                    $extra,
                    $options['type'],
                    $vendorDir.DIRECTORY_SEPARATOR.$package->getName()
                );

                if (\count($extraScripts)) {
                    // @phpstan-ignore-next-line
                    if (isset($extraScripts[$event->getName()])) {
                        // @phpstan-ignore-next-line
                        $scripts = $extraScripts[$event->getName()];

                        foreach ($scripts as $script) {
                            // @phpstan-ignore-next-line
                            if (\in_array($event->getName(), $delayedEvents)) {
                                // @phpstan-ignore-next-line
                                $_scripts[$event->getName()][] = [
                                    'script' => $script,
                                    'event' => $event,
                                ];
                            } elseif (\is_callable($script)) {
                                $className = \substr($script, 0, \strpos($script, '::') ?: 0);
                                $methodName = \substr($script, \strpos($script, '::') + 2);
                                $className::$methodName($event);
                            }
                        }
                    }
                }
            }
        // @phpstan-ignore-next-line
        } elseif (\in_array($event->getName(), ['post-install-cmd', 'post-update-cmd'])) {
            foreach ($_scripts as $eventName => $scripts) {
                foreach ($scripts as $data) {
                    if (\is_callable($data['script']) && \is_string($data['script'])) {
                        $className = \substr($data['script'], 0, \strpos($data['script'], '::') ?: 0);
                        $methodName = \substr($data['script'], \strpos($data['script'], '::') + 2);
                        $className::$methodName($data['event']);
                    }
                }
            }
        }
    }
}
