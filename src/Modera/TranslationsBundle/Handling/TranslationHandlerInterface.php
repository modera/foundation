<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
interface TranslationHandlerInterface
{
    const STRATEGY_SOURCE_TREE = 'source_tree';
    const STRATEGY_RESOURCE_FILES = 'resource_files';

    /**
     * @return string
     */
    public function getBundleName();

    /**
     * @return array
     */
    public function getStrategies();

    /**
     * @return array
     */
    public function getSources();

    /**
     * Copies translations from file system of a symfony dictionary that eventually
     * will be dumped to database.
     *
     * @param string $source
     * @param string $locale
     *
     * @return MessageCatalogueInterface | null
     */
    public function extract($source, $locale);
}
