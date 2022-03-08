<?php

namespace Modera\TranslationsBundle\Compiler\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
class CacheAdapter implements AdapterInterface
{
    const CACHE_KEY = 'modera_translations.cache_adapter';

    private CacheAdapterInterface $cache;

    public function __construct(CacheAdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->cache->deleteItem(self::CACHE_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(MessageCatalogueInterface $catalogue): void
    {
        $catalogues = [];
        $item = $this->cache->getItem(self::CACHE_KEY);
        if ($string = $item->get()) {
            $catalogues = unserialize($string);
        }
        $catalogues[$catalogue->getLocale()] = $catalogue->all();
        $item->set(serialize($catalogues));
        $this->cache->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function loadCatalogue(string $locale): MessageCatalogueInterface
    {
        $item = $this->cache->getItem(self::CACHE_KEY);
        if ($string = $item->get()) {
            $catalogues = unserialize($string);
            if (isset($catalogues[$locale])) {
                return new MessageCatalogue($locale, $catalogues[$locale]);
            }
        }

        return new MessageCatalogue($locale);
    }
}
