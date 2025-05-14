<?php

namespace Modera\BackendTranslationsToolBundle\Tests\Unit\Handling;

use Modera\BackendTranslationsToolBundle\Extractor\ExtjsExtractor;
use Modera\BackendTranslationsToolBundle\Handling\ExtjsTranslationHandler;
use Modera\TranslationsBundle\Handling\PhpClassesTranslationHandler;
use Modera\TranslationsBundle\Handling\TemplateTranslationHandler;
use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Reader\TranslationReader;

class TranslationHandlerTest extends \PHPUnit\Framework\TestCase
{
    private string $bundle;

    private KernelInterface $kernel;

    private ExtractorInterface $extractor;

    private TranslationReader $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bundle = 'BackendTranslationsToolBundle';
        $this->kernel = \Phake::mock(KernelInterface::class);
        \Phake::when($this->kernel)->getBundle($this->bundle)->thenReturn(new DummyBundle());
        $this->extractor = \Phake::mock(ExtjsExtractor::class);
        $this->loader = new TranslationReader();
    }

    public function testExtjsTranslationHandler(): void
    {
        $handler = new ExtjsTranslationHandler($this->kernel, $this->loader, $this->extractor, $this->bundle);

        $this->assertInstanceOf(TranslationHandlerInterface::class, $handler);
        $this->assertEquals($this->bundle, $handler->getBundleName());
        $this->assertEquals([ExtjsTranslationHandler::SOURCE_NAME], $handler->getSources());
        $this->assertNull($handler->extract(TemplateTranslationHandler::SOURCE_NAME, 'en'));
        $this->assertNull($handler->extract(PhpClassesTranslationHandler::SOURCE_NAME, 'en'));
        $catalogue = $handler->extract(ExtjsTranslationHandler::SOURCE_NAME, 'en');
        $this->assertInstanceOf(MessageCatalogueInterface::class, $catalogue);
        $this->assertEquals('en', $catalogue->getLocale());
    }
}

class DummyBundle extends Bundle
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
