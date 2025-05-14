<?php

namespace Modera\TranslationsBundle\Compiler;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * You can use this service to compile translations, under the hood service relies on a console command because
 * some operations cannot be performed in scope of request and a separate process has be to be used instead.
 *
 * @copyright 2016 Modera Foundation
 */
class TranslationsCompiler
{
    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    /**
     * Compiles translations and clears cache for a current environment, newly compiled translations
     * will be detected by Symfony on a next request.
     *
     * @throws \Exception
     */
    public function compile(bool $onlyTranslated = false): CompilationResult
    {
        $app = $this->createApplication();

        $input = new ArrayInput([
            'command' => 'modera:translations:compile',
            '--only-translated' => $onlyTranslated,
            '--no-ansi' => true,
            '-v' => true,
        ]);
        $input->setInteractive(false);

        $compileOutput = new BufferedOutput();

        $compileTranslationsExitCode = $app->run($input, $compileOutput);

        return new CompilationResult($compileTranslationsExitCode, $compileOutput->fetch());
    }

    private function createApplication(): Application
    {
        $app = new Application($this->kernel);
        $app->setAutoExit(false);

        return $app;
    }
}
