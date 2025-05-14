<?php

namespace Modera\TranslationsBundle\Tests\Unit\Compiler;

use Modera\TranslationsBundle\Compiler\CompilationResult;

class CompilationResultTest extends \PHPUnit\Framework\TestCase
{
    public function testGetErrorMessage(): void
    {
        $rawOutput = <<<'LOREM'

  [RuntimeException]
  Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.


Exception trace:
 () at /var/www/vendor/modera/translations-bundle/Modera/TranslationsBundle/Command/CompileTranslationsCommand.php:34
 Modera\TranslationsBundle\Command\CompileTranslationsCommand->execute() at /var/www/vendor/symfony/symfony/src/Symfony/Component/Console/Command/Command.php:259
 Symfony\Component\Console\Command\Command->run() at /var/www/vendor/symfony/symfony/src/Symfony/Component/Console/Application.php:860
 Symfony\Component\Console\Application->doRunCommand() at /var/www/vendor/symfony/symfony/src/Symfony/Component/Console/Application.php:192
 Symfony\Component\Console\Application->doRun() at /var/www/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Console/Application.php:92
 Symfony\Bundle\FrameworkBundle\Console\Application->doRun() at /var/www/vendor/symfony/symfony/src/Symfony/Component/Console/Application.php:123
 Symfony\Component\Console\Application->run() at /var/www/bin/console:29

modera:translations:compile [-h|--help] [-q|--quiet] [-v|vv|vvv|--verbose] [-V|--version] [--ansi] [--no-ansi] [-n|--no-interaction] [-s|--shell] [--process-isolation] [-e|--env ENV] [--no-debug] [--] <command>
LOREM;

        $result = new CompilationResult(1, $rawOutput);

        $this->assertEquals(
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n",
            $result->getErrorMessage()
        );
    }

    public function testIsSuccessful(): void
    {
        $result = new CompilationResult(0, '');

        $this->assertTrue($result->isSuccessful());

        $result = new CompilationResult(1, '');

        $this->assertFalse($result->isSuccessful());
    }
}
