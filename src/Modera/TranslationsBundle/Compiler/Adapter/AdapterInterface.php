<?php

namespace Modera\TranslationsBundle\Compiler\Adapter;

use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @copyright 2019 Modera Foundation
 */
interface AdapterInterface
{
    /**
     * Clear all previous dumped messages.
     */
    public function clear(): void;

    /**
     * Dump messages.
     */
    public function dump(MessageCatalogueInterface $catalogue): void;

    /**
     * Loads the catalogue by locale.
     */
    public function loadCatalogue(string $locale): MessageCatalogueInterface;
}
