<?php

namespace Modera\LanguagesBundle\Composer;

use Composer\Script\Event;
use Modera\ModuleBundle\Composer\AbstractScriptHandler;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2015 Modera Foundation
 */
class ScriptHandler extends AbstractScriptHandler
{
    public static function configSync(Event $event): void
    {
        $options = static::getOptions($event);

        /** @var string $binDir */
        $binDir = $options['symfony-bin-dir'];

        echo '>>> ModeraLanguagesBundle: Config sync'.PHP_EOL;

        if (!\is_dir($binDir)) {
            echo 'The symfony-bin-dir ('.$binDir.') specified in composer.json was not found in '.\getcwd().'.'.PHP_EOL;

            return;
        }

        /** @var int $processTimeout */
        $processTimeout = $options['process-timeout'];

        static::executeCommand($event, $binDir, 'modera:languages:config-sync', $processTimeout);

        echo '>>> ModeraLanguagesBundle: done'.PHP_EOL;
    }
}
