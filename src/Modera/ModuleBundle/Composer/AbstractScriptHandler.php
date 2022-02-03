<?php

namespace Modera\ModuleBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
abstract class AbstractScriptHandler
{
    /**
     * @param Event  $event
     * @param string $consoleDir
     * @param string $cmd
     * @param int    $timeout
     *
     * @throws \RuntimeException
     */
    protected static function executeCommand(Event $event, $consoleDir, $cmd, $timeout = 300)
    {
        $php = escapeshellarg(static::getPhp());
        $console = escapeshellarg($consoleDir.'/console');
        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }

        $command = $php.' '.$console.' '.$cmd;

        $process = Process::fromShellCommandline($command, null, null, null, $timeout);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            $msg = sprintf(
                "An error occurred when executing the \"%s\" command: \n%s\n%s",
                escapeshellarg($cmd), $process->getErrorOutput(), $process->getOutput()
            );

            throw new \RuntimeException($msg);
        }
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    protected static function getOptions(Event $event)
    {
        $options = array_merge(array(
            'symfony-app-dir' => 'app',
            'symfony-bin-dir' => 'bin',
        ), $event->getComposer()->getPackage()->getExtra());

        $options['process-timeout'] = $event->getComposer()->getConfig()->get('process-timeout');

        return $options;
    }

    /**
     * @return false|string
     *
     * @throws \RuntimeException
     */
    protected static function getPhp()
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    protected static function useNewDirectoryStructure(array $options)
    {
        return isset($options['symfony-var-dir']) && is_dir($options['symfony-var-dir']);
    }

    /**
     * @param Event $event
     * @param string $configName
     * @param string $path
     * @param string $actionName
     *
     * @return bool
     */
    protected static function hasDirectory(Event $event, $configName, $path, $actionName = null)
    {
        if (!is_dir($path)) {
            $event->getIO()->write(
                sprintf(
                    'The %s (%s) specified in composer.json was not found in %s.',
                    $configName,
                    $path,
                    getcwd() . ($actionName ? ', can not ' . $actionName : '')
                )
            );
            return false;
        }
        return true;
    }

    /**
     * @param Event  $event
     * @param string $actionName
     *
     * @return string|null
     */
    protected static function getConsoleDir(Event $event, $actionName = null)
    {
        $options = static::getOptions($event);
        if (static::useNewDirectoryStructure($options)) {
            if (!static::hasDirectory($event, 'symfony-bin-dir', $options['symfony-bin-dir'], $actionName)) {
                return;
            }
            return $options['symfony-bin-dir'];
        }
        if (!static::hasDirectory($event, 'symfony-app-dir', $options['symfony-app-dir'], $actionName)) {
            return;
        }
        return $options['symfony-app-dir'];
    }

    /**
     * @param string $handlerName
     * @return mixed
     */
    protected static function getScriptHandler(Event $event, $handlerName)
    {
        $options = static::getOptions($event);
        if (isset($options['modera-module']) && isset($options['modera-module']['script-handler'])) {
            if (isset($options['modera-module']['script-handler'][$handlerName])) {
                return $options['modera-module']['script-handler'][$handlerName];
            }
        }
    }
}
