<?php

namespace Modera\FoundationBundle\Process;

use Symfony\Component\Process\Process;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2018 Modera Foundation
 */
class BackgroundProcess extends Process
{
    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        // overwrite to prevent kill process
    }

    /**
     * {@inheritdoc}
     */
    public function start(callable $callback = null)
    {
        $env = 1 < \func_num_args() ? \func_get_arg(1) : null;

        static::prepare($this);

        return parent::start($callback, $env);
    }

    /**
     * @param Process $process
     */
    public static function prepare(Process $process)
    {
        $commandline = $process->getCommandLine();

        if (substr(strtoupper(PHP_OS), 0, 3) === 'WIN') {
            $process->setCommandLine('START /b "" ' . $commandline);
        } else {
            $process->setCommandLine('nohup ' . $commandline . ' >/dev/null 2>&1 & echo $!');
        }
    }
}
