<?php

namespace Modera\TranslationsBundle\Handling;

use Modera\TranslationsBundle\TokenExtraction\JsonFileExtractor;

/**
 * @copyright 2025 Modera Foundation
 */
class JsonFileTranslationHandler extends ResourcesTranslationHandler
{
    /**
     * @param string[] $resources
     * @param string[] $strategies
     */
    public function __construct(
        JsonFileExtractor $extractor,
        string $bundle,
        iterable $resources = [],
        array $strategies = [self::STRATEGY_SOURCE_TREE],
    ) {
        parent::__construct($extractor, 'json-file', $bundle, $resources, $strategies);
    }
}
