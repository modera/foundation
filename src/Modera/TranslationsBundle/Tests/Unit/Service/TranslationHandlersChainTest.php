<?php

namespace Modera\TranslationsBundle\Tests\Unit\Service;

use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;
use Modera\TranslationsBundle\Service\TranslationHandlersChain;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TranslationHandlersChainTest extends \PHPUnit\Framework\TestCase
{
    public function testHandlers()
    {
        $handlersChain = new TranslationHandlersChain();
        $this->assertEquals(0, count($handlersChain->getHandlers()));

        $handlersChain->addHandler(new DummyHandler('test1'));
        $this->assertEquals(1, count($handlersChain->getHandlers()));

        $handlersChain->addHandler(new \stdClass('test2'));
        $this->assertEquals(1, count($handlersChain->getHandlers()));

        $handlersChain->addHandler(new DummyHandler('test3'));
        $this->assertEquals(2, count($handlersChain->getHandlers()));
    }
}

class DummyHandler implements TranslationHandlerInterface
{
    private string $bundle;

    public function __construct(string $bundle)
    {
        $this->bundle = $bundle;
    }

    public function getBundleName(): string
    {
        return $this->bundle;
    }

    public function getStrategies(): array
    {
        return array(static::STRATEGY_SOURCE_TREE);
    }

    public function getSources(): array
    {
        return array('test');
    }

    public function extract(string $source, string $locale): ?MessageCatalogueInterface
    {
        return null;
    }
}
