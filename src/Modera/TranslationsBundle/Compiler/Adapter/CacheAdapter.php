<?php

namespace Modera\TranslationsBundle\Compiler\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @copyright 2022 Modera Foundation
 */
class CacheAdapter implements AdapterInterface
{
    public const CACHE_KEY = 'modera_translations.cache_adapter';

    public function __construct(
        private readonly CacheAdapterInterface $cache,
    ) {
    }

    public function clear(): void
    {
        $this->cache->deleteItem(self::CACHE_KEY);
    }

    public function dump(MessageCatalogueInterface $catalogue): void
    {
        /** @var array<string, array<mixed>> $catalogues */
        $catalogues = [];
        $item = $this->cache->getItem(self::CACHE_KEY);
        if ($string = $item->get()) {
            /** @var string $string */
            /** @var array<string, array<mixed>> $catalogues */
            $catalogues = \unserialize($string);
        }
        $catalogues[$catalogue->getLocale()] = $catalogue->all();
        $item->set(\serialize($catalogues));
        $this->cache->save($item);
    }

    public function loadCatalogue(string $locale): MessageCatalogueInterface
    {
        $item = $this->cache->getItem(self::CACHE_KEY);
        if ($string = $item->get()) {
            /** @var string $string */
            /** @var array<string, array<mixed>> $catalogues */
            $catalogues = \unserialize($string);
            if (isset($catalogues[$locale])) {
                return new MessageCatalogue($locale, $catalogues[$locale]);
            }
        }

        return new MessageCatalogue($locale);
    }
}
