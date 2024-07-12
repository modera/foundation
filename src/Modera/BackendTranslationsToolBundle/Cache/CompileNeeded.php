<?php

namespace Modera\BackendTranslationsToolBundle\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class CompileNeeded
{
    private const COMPILE_NEEDED_KEY = 'modera_backend_translations_tool.compile_needed';

    private AdapterInterface $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    public function set(bool $value): void
    {
        $this->cache->save($this->cache->getItem(self::COMPILE_NEEDED_KEY)->set($value));
    }

    public function get(): bool
    {
        $value = false;

        $item = $this->cache->getItem(self::COMPILE_NEEDED_KEY);
        if ($item->isHit()) {
            $value = (bool) $item->get();
        }

        return $value;
    }
}
