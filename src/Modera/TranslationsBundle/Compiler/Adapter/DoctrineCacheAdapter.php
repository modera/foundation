<?php

namespace Modera\TranslationsBundle\Compiler\Adapter;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class DoctrineCacheAdapter implements AdapterInterface
{
    const CACHE_KEY = 'modera_translations.doctrine_cache_adapter';

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function clear()
    {
        $this->cache->delete(self::CACHE_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(MessageCatalogueInterface $catalogue)
    {
        $catalogues = [];
        if ($string = $this->cache->fetch(self::CACHE_KEY)) {
            $catalogues = unserialize($string);
        }

        $catalogues[$catalogue->getLocale()] = $catalogue->all();

        $this->cache->save(self::CACHE_KEY, serialize($catalogues));
    }

    /**
     * {@inheritdoc}
     */
    public function loadCatalogue($locale)
    {
        if ($string = $this->cache->fetch(self::CACHE_KEY)) {
            $catalogues = unserialize($string);
            if (isset($catalogues[$locale])) {
                return new MessageCatalogue($locale, $catalogues[$locale]);
            }
        }

        return new MessageCatalogue($locale);
    }
}
