<?php

namespace Modera\TranslationsBundle\TokenExtraction;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Attempts to extract translation tokens from PHP classes. This is what you should do to let this extractor
 * detect and extract tokens from your PHP classes:.
 *
 * 1) Import T class from Helper package, for example:
 *    use Modera\FoundationBundle\Translation\T;
 *
 * Do not use aliases! For example, this won't be detected ( yet ):
 *    use Modera\FoundationBundle\Translation\T as Translator;
 *
 * 2) In your code use method T::trans(). Samples:
 *
 *    By using string literals:
 *      T::trans('Hello')
 *      T::trans('Hello, %name%', array('name' => 'Bob'))
 *      T::trans('Achtung!', null, 'errors')
 *
 *    If you have a long message then you can use this syntax:
 *      $message = 'Achtung! ';
 *      $message.= 'Dear %name%, ';
 *      $message.= "you don't have anough privileges to perform this action!";
 *
 *      T::trans($mesage, array('name' => 'Bob'));
 *
 *    When this code is parsed you will get this translation token:
 *    Achtung! Dear %name%, you don't have anough privileges to perform this action!
 *
 *    Please keep in mind, that you can't when perform any manipulations or string that are going to be
 *    part of token, for example, you can't do this:
 *      $message = ucfirst('Achtung!');
 *    When tokens are being extracted from code it is being statically analyzed when when functions are invoked
 *    their values will be resolved during execution phase.
 *
 * @copyright 2014 Modera Foundation
 */
class PhpClassTokenExtractor implements ExtractorInterface
{
    /**
     * Prefix for new-found message.
     */
    private string $prefix = '';

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function extract($resource, MessageCatalogue $catalogue): void
    {
        if (\is_iterable($resource)) {
            foreach ($resource as $dir) {
                $this->extract($dir, $catalogue);
            }

            return;
        }

        // load any existing translation files
        $finder = new Finder();
        $files = $finder->files()->name('*.php')->exclude('Tests')->in((string) $resource);
        foreach ($files as $file) {
            $this->parseTokens(
                \token_get_all(\file_get_contents($file) ?: ''),
                $catalogue
            );
        }
    }

    /**
     * Normalizes a token.
     *
     * @param string|array<int, int|string> $token
     */
    protected function normalizeToken($token): string
    {
        if (\is_array($token)) {
            return (string) $token[1];
        }

        return $token;
    }

    /**
     * @param array<int, string|array<int, int|string>> $tokens
     *
     * @return array<int, array<string, int|string|array<mixed>|null>>
     */
    private function extractInvocations(array $tokens): array
    {
        $sequences = [
            ['T', '::', 'trans'],
        ];

        $invocations = [];

        foreach ($sequences as $seq) {
            foreach ($tokens as $tokenIndex => $token) {
                $matchCount = 0;
                foreach ($seq as $seqIndex => $item) {
                    $indexToValidate = $tokenIndex + $seqIndex; // next token in a token stream

                    if (isset($tokens[$indexToValidate]) && $this->normalizeToken($tokens[$indexToValidate]) === $item) {
                        ++$matchCount;
                    }
                }

                // we will continue only if we got exact match for entire sequence of tokens
                if ($matchCount !== \count($seq)) {
                    continue;
                }

                $startIndex = $tokenIndex + \count($seq);

                if ('(' !== $tokens[$startIndex]) {
                    continue;
                }

                ++$startIndex;
                $depth = 1; // because there was already one "("
                $bodyLength = null;

                $bodyStartTokens = \array_slice($tokens, $startIndex);
                foreach ($bodyStartTokens as $braceWannaBeIndex => $braceWannaBeToken) {
                    $value = $this->normalizeToken($braceWannaBeToken);

                    if ('(' === $value) {
                        ++$depth;
                    } elseif (')' === $value) {
                        --$depth;
                    }

                    if (0 === $depth) {
                        $bodyLength = (int) $braceWannaBeIndex;
                        break;
                    }
                }

                $bodyTokens = \array_slice($tokens, $startIndex, $bodyLength);

                $invocations[] = [
                    'method_name' => $seq[\count($seq) - 1],
                    'start_index' => $startIndex,
                    'body' => $bodyTokens,
                    'length' => $bodyLength,
                    'tokens' => $tokens,
                ];
            }
        }

        return $invocations;
    }

    /**
     * Will filter out whitespace tokens because we don't use them in during tokens stream analysis.
     *
     * @param array<mixed> $tokens
     *
     * @return array<mixed>
     */
    private function siftOutWhitespaceTokens(array $tokens): array
    {
        $result = [];

        foreach ($tokens as $token) {
            if (\is_array($token) && \T_WHITESPACE === $token[0]) {
                continue;
            }

            $result[] = $token;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $invocation
     *
     * @return array<string, mixed>
     */
    private function extractArgumentTokens(array $invocation): array
    {
        $tokens = (array) $invocation['body'];

        // if method contains no parameters, like Helper::trans()
        if (0 === \count($tokens)) {
            return [];
        }

        $args = [
            'token' => $tokens[0],
            'params' => [],
            'domain' => [],
        ];

        // first argument is "message":
        // trans($id, $parameters, $domain, $locale)

        $indexShift = 0;

        if (\is_array($tokens[0]) && (\T_STRING === $tokens[0][0] || \T_NAME_FULLY_QUALIFIED === $tokens[0][0])) {
            if (\in_array($tokens[0][1], ['implode', '\implode'])) {
                $indexShift += (int) \array_search(')', $tokens, true) + ('[' === $tokens[4] ? 0 : 1);
            }
        }

        $isParamsArgSpecified = isset($tokens[$indexShift + 1])
                            && ',' === $tokens[$indexShift + 1]
                            && isset($tokens[$indexShift + 2])
        ;

        if ($isParamsArgSpecified) {
            $isArrayParameter = 'array' === \strtolower($this->normalizeToken($tokens[$indexShift + 2]))
                            && isset($tokens[$indexShift + 3])
                            && '(' === $this->normalizeToken($tokens[$indexShift + 3])
            ;

            if (!$isArrayParameter) {
                $isArrayParameter = '[' === $this->normalizeToken($tokens[$indexShift + 2]);
            }

            $isNullParameter = 'null' === \strtolower($this->normalizeToken($tokens[$indexShift + 2]));
            $isVariableParameter = \T_VARIABLE === $tokens[$indexShift + 2][0];

            if ($isArrayParameter) {
                $depth = 1;
                $secondArgEndIndex = null;

                $j = '[' === $this->normalizeToken($tokens[$indexShift + 2]) ? 3 : 4;
                for ($i = $indexShift + $j; $i < \count($tokens); ++$i) {
                    $value = $this->normalizeToken($tokens[$i]);

                    // parameters may be nested
                    if ('(' === $value) {
                        ++$depth;
                    } elseif (')' === $value) {
                        --$depth;
                    } elseif ('[' === $value) {
                        ++$depth;
                    } elseif (']' === $value) {
                        --$depth;
                    }

                    if (0 === $depth) {
                        $secondArgEndIndex = $i;
                        break;
                    }
                }

                // token parameters
                $args['params'] = \array_slice($tokens, $indexShift + $j, $secondArgEndIndex);

                // if $params argument is followed by "," we assume that domain is specified
                $isDomainArgumentSpecified = isset($tokens[$indexShift + $secondArgEndIndex + 1])
                                        && ',' === $tokens[$indexShift + $secondArgEndIndex + 1]
                                        && isset($tokens[$indexShift + $secondArgEndIndex + 2])
                ;

                if ($isDomainArgumentSpecified) {
                    $args['domain'] = $tokens[$indexShift + $secondArgEndIndex + 2];
                }
            } elseif ($isNullParameter || $isVariableParameter) { // second parameter is
                $args['params'] = $tokens[$indexShift + 2];

                // if params are followed by "null," we assume that domain parameter is also provided
                $isDomainArgumentSpecified = isset($tokens[$indexShift + 3])
                                        && ',' === $this->normalizeToken($tokens[$indexShift + 3])
                                        && isset($tokens[$indexShift + 4])
                ;

                if ($isDomainArgumentSpecified) {
                    $args['domain'] = $tokens[$indexShift + 4];
                }
            }
        }

        return $args;
    }

    /**
     * @param array<int, string|array<int, int|string>> $tokens
     */
    private function resolveImplodeFn(array $tokens): string
    {
        $glue = '';
        if (\T_CONSTANT_ENCAPSED_STRING === $tokens[2][0]) {
            $glue = \substr((string) $tokens[2][1], 1, -1);
        } elseif (\T_STRING === $tokens[2][0]) {
            $glue = \constant((string) $tokens[2][1]);
        }
        if (!\is_string($glue)) {
            throw new \RuntimeException('$glue must be a string');
        }

        $offset = '[' === $tokens[4] ? 5 : 6;
        $length = '[' === $tokens[4] ? -3 : -2;

        $pieces = [];
        foreach (\array_slice($tokens, $offset, $length) as $val) {
            if (\is_array($val)) {
                $pieces[] = \substr((string) $val[1], 1, -1);
            }
        }

        return '\''.\implode($glue, $pieces).'\'';
    }

    /**
     * @param array<int, int|string> $valueToken
     * @param array<string, mixed>   $invocation
     */
    private function resolveTokenValue(array $valueToken, array $invocation): string
    {
        if (\T_CONSTANT_ENCAPSED_STRING === $valueToken[0]) {
            // just a string literal
            $value = (string) $valueToken[1];

            return \trim($value, $value[0]);
        } elseif (\T_STRING === $valueToken[0] || \T_NAME_FULLY_QUALIFIED === $valueToken[0]) {
            if (\in_array($valueToken[1], ['implode', '\implode'])) {
                /** @var int $startIndex */
                $startIndex = $invocation['start_index'];
                /** @var array<int, string|array<int, int|string>> $tokens */
                $tokens = $invocation['tokens'];
                $limit = (int) \array_search(')', \array_slice($tokens, $startIndex), true) + 2;
                $value = $this->resolveImplodeFn(\array_slice($tokens, $startIndex, $limit));

                return \trim($value, $value[0]);
            }
        } elseif (\T_VARIABLE === $valueToken[0]) {
            // variable is used, we are going to try to resolve its value even if it is composite
            // ( made up of several assign statements )

            $variableName = $valueToken[1];

            /** @var int $startIndex */
            $startIndex = $invocation['start_index'];
            /** @var array<int, string|array<int, int|string>> $tokens */
            $tokens = $invocation['tokens'];

            // narrowing variable value assign. zone
            $parentTokens = \array_slice($tokens, 0, $startIndex);
            $parentTokens = \array_reverse($parentTokens);

            $length = null;
            foreach ($parentTokens as $i => $parentToken) {
                if (\is_array($parentToken) && \T_FUNCTION === $parentToken[0]) {
                    $length = $i;
                    break;
                }
            }

            $parentTokens = \array_slice($parentTokens, 0, $length);
            $parentTokens = \array_reverse($parentTokens);

            // now that we have all tokens from FUNCTION to the Helper::*() we can compile variable's value

            $variableValue = '';
            foreach ($parentTokens as $i => $parentToken) {
                // ha, this is our variable!
                if (\is_array($parentToken) && \T_VARIABLE === $parentToken[0] && $parentToken[1] === $variableName) {
                    // both assign operator and a value exist
                    if (isset($parentTokens[$i + 1]) && isset($parentTokens[$i + 2])) {
                        $assignValueTokenValue = $parentTokens[$i + 1];
                        $variableValueTokenValue = $parentTokens[$i + 2];

                        $isValidAssignToken = false;
                        if (\is_string($assignValueTokenValue)) {
                            $isValidAssignToken = '=' === $assignValueTokenValue;
                        } elseif (\is_array($assignValueTokenValue)) {
                            $isValidAssignToken = \T_CONCAT_EQUAL === $assignValueTokenValue[0];
                        }

                        // we are not going to support assign statement when one variable points to another etc
                        $isValidVarValueToken = \is_array($variableValueTokenValue)
                                            && \T_CONSTANT_ENCAPSED_STRING === $variableValueTokenValue[0]
                        ;

                        if (!$isValidVarValueToken && (\T_STRING === $variableValueTokenValue[0] || \T_NAME_FULLY_QUALIFIED === $variableValueTokenValue[0])) {
                            if (\in_array($variableValueTokenValue[1], ['implode', '\implode'])) {
                                $offset = $i + 2;
                                $limit = \array_search(')', \array_slice($parentTokens, $offset), true) + 2;
                                $variableValueTokenValue = $this->resolveImplodeFn(\array_slice($parentTokens, $offset, $limit));
                                $isValidVarValueToken = true;
                            }
                        }

                        if ($isValidAssignToken && $isValidVarValueToken) {
                            $value = $this->normalizeToken($variableValueTokenValue);
                            $assignStmt = $this->normalizeToken($assignValueTokenValue);

                            if ('=' === $assignStmt) {
                                $variableValue = \trim($value, $value[0]);
                            } elseif ('.=' === $assignStmt) {
                                $variableValue .= \trim($value, $value[0]);
                            }
                        }
                    }
                }
            }

            return $variableValue;
        }

        return 'Error! Token value can be either a literal string or variable reference.';
    }

    /**
     * Will make sure if a token stream which represents a file has required USE statement.
     *
     * @param array<int, mixed> $tokens
     */
    private function containsRequiredUseStatements(array $tokens): bool
    {
        foreach ($tokens as $currentIndex => $token) {
            if (!\is_array($token)) {
                continue;
            }

            if (\T_USE === $token[0]) {
                $expectedSequence = ['Modera', '\\', 'FoundationBundle', '\\', 'Translation', '\\', 'T'];
                $expectedLength = [
                    1,                        // >= PHP 8
                    \count($expectedSequence), // <= PHP 7
                ];
                foreach ($expectedLength as $length) {
                    $currentSequence = \array_slice($tokens, $currentIndex + 1, $length);
                    if (\implode('', $expectedSequence) === $this->joinTokenSequence($currentSequence)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param array<mixed> $tokenSequence
     */
    private function joinTokenSequence(array $tokenSequence): string
    {
        $result = [];

        foreach ($tokenSequence as $token) {
            $result[] = \is_array($token) ? $token[1] : $token;
        }

        return \implode('', $result);
    }

    /**
     * @param array<mixed> $tokens
     */
    private function parseTokens(array $tokens, MessageCatalogue $catalog): void
    {
        /** @var array<int, string|array<int, int|string>> $tokens */
        $tokens = $this->siftOutWhitespaceTokens($tokens);

        if (!$this->containsRequiredUseStatements($tokens)) {
            return;
        }

        $invocations = $this->extractInvocations($tokens);

        foreach ($invocations as $invocation) {
            $argumentsTokens = $this->extractArgumentTokens($invocation);
            if (0 === \count($argumentsTokens)) {
                continue;
            }

            /** @var array<int, int|string> $valueToken */
            $valueToken = $argumentsTokens['token'];
            $tokenValue = $this->resolveTokenValue($valueToken, $invocation);
            if (!$tokenValue) {
                continue;
            }

            $domain = 'messages';
            /** @var array<int, int|string> $domainTokens */
            $domainTokens = $argumentsTokens['domain'];
            if (\count($domainTokens) > 0) {
                $isNullParameter = 'null' === \strtolower($this->normalizeToken($domainTokens));
                if (!$isNullParameter) {
                    $domain = $this->resolveTokenValue($domainTokens, $invocation);
                }
            }

            $catalog->set($this->prefix.$tokenValue, $tokenValue, $domain);
        }
    }
}
