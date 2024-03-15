<?php

namespace Modera\ModuleBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
abstract class AbstractScriptHandler
{
    /**
     * @throws \RuntimeException
     */
    protected static function executeCommand(Event $event, string $consoleDir, string $cmd, int $timeout = 300): void
    {
        $php = \escapeshellarg(static::getPhp());
        $console = \escapeshellarg($consoleDir.'/console');
        // @phpstan-ignore-next-line
        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }

        $command = $php.' '.$console.' '.$cmd;

        if (\method_exists(Process::class, 'fromShellCommandline')) {
            $process = Process::fromShellCommandline($command, null, null, null, $timeout);
        } else {
            // @phpstan-ignore-next-line
            $process = new Process($command, null, null, null, $timeout);
        }

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            $msg = \sprintf(
                "An error occurred when executing the \"%s\" command: \n%s\n%s",
                \escapeshellarg($cmd),
                $process->getErrorOutput(),
                $process->getOutput()
            );

            throw new \RuntimeException($msg);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected static function getOptions(Event $event): array
    {
        // @phpstan-ignore-next-line
        $extra = $event->getComposer()->getPackage()->getExtra();

        $options = \array_merge([
            'symfony-app-dir' => 'app',
            'symfony-bin-dir' => 'bin',
        ], $extra);

        // @phpstan-ignore-next-line
        $processTimeout = $event->getComposer()->getConfig()->get('process-timeout');

        $options['process-timeout'] = $processTimeout;

        return $options;
    }

    /**
     * @throws \RuntimeException
     */
    protected static function getPhp(): string
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected static function useNewDirectoryStructure(array $options): bool
    {
        /** @var array{'symfony-var-dir'?: string} $options */
        $options = $options;

        return isset($options['symfony-var-dir']) && \is_dir($options['symfony-var-dir']);
    }

    protected static function hasDirectory(Event $event, string $configName, string $path, ?string $actionName = null): bool
    {
        if (!\is_dir($path)) {
            // @phpstan-ignore-next-line
            $event->getIO()->write(
                \sprintf(
                    'The %s (%s) specified in composer.json was not found in %s.',
                    $configName,
                    $path,
                    \getcwd().($actionName ? ', can not '.$actionName : '')
                )
            );

            return false;
        }

        return true;
    }

    protected static function getConsoleDir(Event $event, ?string $actionName = null): ?string
    {
        $options = static::getOptions($event);
        if (static::useNewDirectoryStructure($options)) {
            /** @var array{'symfony-bin-dir': string} $options */
            if (!static::hasDirectory($event, 'symfony-bin-dir', $options['symfony-bin-dir'], $actionName)) {
                return null;
            }

            return $options['symfony-bin-dir'];
        }
        /** @var array{'symfony-app-dir': string} $options */
        if (!static::hasDirectory($event, 'symfony-app-dir', $options['symfony-app-dir'], $actionName)) {
            return null;
        }

        return $options['symfony-app-dir'];
    }

    /**
     * @return mixed Mixed value
     */
    protected static function getScriptHandler(Event $event, string $handlerName)
    {
        /** @var array{'modera-module'?: array{'script-handler'?: array<string, mixed>}} $options */
        $options = static::getOptions($event);
        if (isset($options['modera-module']) && isset($options['modera-module']['script-handler'])) {
            if (isset($options['modera-module']['script-handler'][$handlerName])) {
                return $options['modera-module']['script-handler'][$handlerName];
            }
        }
    }
}
