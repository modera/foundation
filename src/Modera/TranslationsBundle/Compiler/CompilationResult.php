<?php

namespace Modera\TranslationsBundle\Compiler;

/**
 * You can use this instance of this class to get information regarding translations compilation result. Usually
 * you won't want to create instances of this class manually, but instead use AsyncTranslationsCompiler service.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class CompilationResult
{
    private int $exitCode;

    private string $rawOutput;

    /**
     * @internal
     */
    public function __construct(int $exitCode, string $rawOutput)
    {
        $this->exitCode = $exitCode;
        $this->rawOutput = $rawOutput;
    }

    public function isSuccessful(): bool
    {
        return 0 === $this->exitCode;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Returns console command output as is.
     */
    public function getRawOutput(): string
    {
        return $this->rawOutput;
    }

    /**
     * Extracts exception error message from command's output.
     */
    public function getErrorMessage(): string
    {
        // extracting message manually seemed like a faster solution than adding "format" support
        // to console command
        if ($this->isSuccessful()) {
            return '';
        }

        $splitOutput = \explode(PHP_EOL, $this->getRawOutput());
        $startIndex = null;
        $endIndex = null;
        foreach ($splitOutput as $i => $line) {
            $line = \trim($line);

            if (null === $startIndex && \preg_match('/\.*\[.+\].*/', $line)) {
                $startIndex = $i + 1;
            }

            if (null !== $startIndex && 'Exception trace:' === $line) {
                $endIndex = $i - 1;
            }
        }

        if (null !== $startIndex && null !== $endIndex) {
            $extractedChunk = \array_slice($splitOutput, $startIndex, $endIndex - $startIndex);

            foreach ($extractedChunk as $i => $value) {
                $extractedChunk[$i] = \trim($value);
            }

            return \implode("\n", $extractedChunk);
        }

        return '';
    }
}
