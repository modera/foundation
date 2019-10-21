<?php

namespace Modera\TranslationsBundle\Compiler\Adapter;

use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
interface AdapterInterface
{
    /**
     * Clear all previous dumped messages
     */
    public function clear();

    /**
     * Dump messages
     *
     * @param MessageCatalogueInterface $catalogue
     */
    public function dump(MessageCatalogueInterface $catalogue);

    /**
     * @param string $locale
     * @return MessageCatalogueInterface
     */
    public function loadCatalogue($locale);
}
