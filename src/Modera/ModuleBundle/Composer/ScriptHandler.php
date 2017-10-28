<?php

namespace Modera\ModuleBundle\Composer;

use Composer\Composer;
use Composer\Script\Event;
use Composer\Json\JsonFile;
use Composer\Installer\PackageEvent;
use Composer\EventDispatcher\Event as BaseEvent;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Modera\Module\Service\ComposerService;
use Modera\ModuleBundle\Composer\Script\AliasPackageEvent;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2013 Modera Foundation
 */
class ScriptHandler extends AbstractScriptHandler
{
    /**
     * @param Event $event
     * @param $value
     */
    private static function setMaintenance(Event $event, $value)
    {
        $options = static::getOptions($event);
        $path = $options['incenteev-parameters']['file'];

        $data = Yaml::parse(file_get_contents($path));
        $data['parameters']['maintenance'] = $value;

        file_put_contents($path, Yaml::dump($data));
    }

    /**
     * @param Event $event
     */
    public static function enableMaintenance(Event $event)
    {
        echo '*** Enable maintenance'.PHP_EOL;

        try {
            static::setMaintenance($event, true);
            static::clearCache($event);
        } catch (\RuntimeException $e) {
            echo $e->getMessage().PHP_EOL;
        }
    }

    /**
     * @param Event $event
     */
    public static function disableMaintenance(Event $event)
    {
        echo '*** Disable maintenance'.PHP_EOL;

        try {
            static::setMaintenance($event, false);
            static::clearCache($event);
        } catch (\RuntimeException $e) {
            echo $e->getMessage().PHP_EOL;
        }
    }

    /**
     * @param PackageEvent $event
     */
    public static function packageEventDispatcher(PackageEvent $event)
    {
        static::baseEventDispatcher($event);
    }

    /**
     * @param Event $event
     */
    public static function eventDispatcher(Event $event)
    {
        static::baseEventDispatcher($event);
    }

    /**
     * @param array  $extra
     * @param string $type
     * @param string $packageDir
     *
     * @return array
     */
    private static function combineScripts(array $extra, $type, $packageDir)
    {
        $scripts = array();
        if (isset($extra[$type])) {
            if (isset($extra[$type]['scripts'])) {
                if (is_array($extra[$type]['scripts'])) {
                    foreach ($extra[$type]['scripts'] as $event => $handler) {
                        if (!isset($scripts[$event])) {
                            $scripts[$event] = array();
                        }

                        if (!is_array($handler)) {
                            $handler = array($handler);
                        }

                        $scripts[$event] = array_merge($scripts[$event], $handler);
                    }
                }
            }

            if (isset($extra[$type]['include'])) {
                $patterns = array();
                foreach ($extra[$type]['include'] as $path) {
                    $patterns[] = $packageDir.DIRECTORY_SEPARATOR.$path;
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
                        foreach (static::combineScripts($json['extra'], $type, dirname($path)) as $event => $handler) {
                            if (!isset($scripts[$event])) {
                                $scripts[$event] = array();
                            }

                            if (!is_array($handler)) {
                                $handler = array($handler);
                            }

                            $scripts[$event] = array_merge($scripts[$event], $handler);
                        }
                    }
                }
            }
        }

        return $scripts;
    }

    /**
     * @param BaseEvent $event
     */
    private static function baseEventDispatcher(BaseEvent $event)
    {
        static $_scripts = array();

        if ($event instanceof PackageEvent) {
            $event = new AliasPackageEvent($event);
        }

        if ($event instanceof AliasPackageEvent) {
            $operation = $event->getAliasOf()->getOperation();
            if ($operation instanceof UpdateOperation) {
                $package = $operation->getTargetPackage();
            } else {
                $package = $operation->getPackage();
            }

            $options = ComposerService::getOptions($event->getComposer());
            if ($package->getType() != $options['type']) {
                //return;
            }

            $extra = $package->getExtra();
            $delayedEvents = array('post-package-install', 'post-package-update');

            if (is_array($extra) && isset($extra[$options['type']])) {
                $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
                $extraScripts = static::combineScripts(
                    $extra, $options['type'], $vendorDir.DIRECTORY_SEPARATOR.$package->getName()
                );

                if (count($extraScripts)) {
                    if (isset($extraScripts[$event->getName()])) {
                        $scripts = $extraScripts[$event->getName()];

                        foreach ($scripts as $script) {
                            if (in_array($event->getName(), $delayedEvents)) {
                                $_scripts[$event->getName()][] = array(
                                    'script' => $script,
                                    'event' => $event,
                                );
                            } elseif (is_callable($script)) {
                                $className = substr($script, 0, strpos($script, '::'));
                                $methodName = substr($script, strpos($script, '::') + 2);
                                $className::$methodName($event);
                            }
                        }
                    }
                }
            }
        } elseif (in_array($event->getName(), array('post-install-cmd', 'post-update-cmd'))) {
            foreach ($_scripts as $eventName => $scripts) {
                foreach ($scripts as $data) {
                    if (is_callable($data['script'])) {
                        $className = substr($data['script'], 0, strpos($data['script'], '::'));
                        $methodName = substr($data['script'], strpos($data['script'], '::') + 2);
                        $className::$methodName($data['event']);
                    }
                }
            }
        }
    }

    /**
     * @param Event $event
     */
    public static function registerBundles(Event $event)
    {
        $options = static::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            self::reportSymfonyAppDirNotFound($appDir);

            return;
        }

        $bundlesFile = 'AppModuleBundles.php';
        $bundles = ComposerService::getRegisterBundles($event->getComposer());

        static::createRegisterBundlesFile($bundles, $appDir.'/'.$bundlesFile);
        static::executeCommand($event, $appDir, 'modera:module:register '.$bundlesFile, $options['process-timeout']);
    }

    /**
     * @param array $bundles
     * @param $outputFile
     */
    private static function createRegisterBundlesFile(array $bundles, $outputFile)
    {
        $data = array('<?php return array(');
        foreach ($bundles as $bundleClassName) {
            $data[] = '    new '.$bundleClassName.'(),';
        }
        $data[] = ');';

        $fs = new Filesystem();
        $fs->dumpFile($outputFile, implode("\n", $data)."\n");

        if (!$fs->exists($outputFile)) {
            throw new \RuntimeException(sprintf('The "%s" file must be created.', $outputFile));
        }
    }

    /**
     * Clears the Symfony cache.
     *
     * @param $event Event A instance
     */
    public static function clearCache(Event $event)
    {
        $options = static::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            self::reportSymfonyAppDirNotFound($appDir);

            return;
        }

        static::executeCommand($event, $appDir, 'cache:clear --env=prod --no-warmup --quiet', $options['process-timeout']);
    }

    /**
     * Executes the SQL needed to update the database schema to match the current mapping metadata.
     *
     * @param $event Event A instance
     */
    public static function doctrineSchemaUpdate(Event $event)
    {
        $options = static::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            self::reportSymfonyAppDirNotFound($appDir);

            return;
        }

        static::executeCommand($event, $appDir, 'doctrine:schema:update --force', $options['process-timeout']);
    }

    /**
     * Creates the configured databases and executes the SQL needed to update the database schema, if database not created.
     *
     * @param Event $event
     */
    public static function initDatabase(Event $event)
    {
        $options = static::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            self::reportSymfonyAppDirNotFound($appDir);

            return;
        }

        try {
            static::executeCommand($event, $appDir, 'doctrine:database:create --quiet', $options['process-timeout']);
        } catch (\RuntimeException $e) {
            // The command throws an exception if database already exists, so here we are supressing it
        }

        try {
            static::doctrineSchemaUpdate($event);
        } catch (\Exception $e) {
            echo "Error during database initialization: ".$e->getMessage()."\n";
        }
    }

    private static function reportSymfonyAppDirNotFound($appDir)
    {
        echo sprintf(
            "The symfony-app-dir (%s) specified in composer.json was not found in %s\n",
            $appDir, getcwd()
        );
    }
}
