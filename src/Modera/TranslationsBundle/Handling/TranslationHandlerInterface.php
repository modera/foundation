<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @copyright 2014 Modera Foundation
 */
interface TranslationHandlerInterface
{
    public const STRATEGY_SOURCE_TREE = 'source_tree';
    public const STRATEGY_RESOURCE_FILES = 'resource_files';

    public function getBundleName(): string;

    /**
     * @return string[]
     */
    public function getStrategies(): array;

    /**
     * @return string[]
     */
    public function getSources(): array;

    /**
     * Copies translations from file system of a symfony dictionary that eventually
     * will be dumped to database.
     */
    public function extract(string $source, string $locale): ?MessageCatalogueInterface;
}
