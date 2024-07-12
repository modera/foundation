<?php

namespace Modera\TranslationsBundle\Compiler\Adapter;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @deprecated https://github.com/doctrine/cache
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class DoctrineCacheAdapter implements AdapterInterface
{
    public const CACHE_KEY = 'modera_translations.doctrine_cache_adapter';

    private Cache $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function clear(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }

    public function dump(MessageCatalogueInterface $catalogue): void
    {
        /** @var array<string, array<mixed>> $catalogues */
        $catalogues = [];
        if ($string = $this->cache->fetch(self::CACHE_KEY)) {
            /** @var string $string */
            /** @var array<string, array<mixed>> $catalogues */
            $catalogues = \unserialize($string);
        }
        $catalogues[$catalogue->getLocale()] = $catalogue->all();
        $this->cache->save(self::CACHE_KEY, \serialize($catalogues));
    }

    public function loadCatalogue(string $locale): MessageCatalogueInterface
    {
        if ($string = $this->cache->fetch(self::CACHE_KEY)) {
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
