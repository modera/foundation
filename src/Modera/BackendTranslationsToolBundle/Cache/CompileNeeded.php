<?php

namespace Modera\BackendTranslationsToolBundle\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class CompileNeeded
{
    const COMPILE_NEEDED_KEY = 'modera_backend_translations_tool.compile_needed';

    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * @param AdapterInterface $cache
     */
    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param bool $value
     */
    public function set($value)
    {
        $this->cache->save($this->cache->getItem(self::COMPILE_NEEDED_KEY)->set($value));
    }

    /**
     * @return bool
     */
    public function get()
    {
        $value = false;

        $item = $this->cache->getItem(self::COMPILE_NEEDED_KEY);
        if ($item->isHit()) {
            $value = $item->get();
        }

        return $value;
    }
}
