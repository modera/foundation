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
     * @param string $appDir
     * @param string $cmd
     * @param int    $timeout
     *
     * @throws \RuntimeException
     */
    protected static function executeCommand(Event $event, $appDir, $cmd, $timeout = 300)
    {
        $php = escapeshellarg(static::getPhp());
        $console = escapeshellarg($appDir.'/console');
        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }

        $command = $php.' '.$console.' '.$cmd;

        $process = new Process($command, null, null, null, $timeout);
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
}
