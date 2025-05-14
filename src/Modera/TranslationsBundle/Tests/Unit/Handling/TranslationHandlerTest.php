<?php

namespace Modera\TranslationsBundle\Tests\Unit\Handling;

use Modera\TranslationsBundle\Handling\PhpClassesTranslationHandler;
use Modera\TranslationsBundle\Handling\TemplateTranslationHandler;
use Modera\TranslationsBundle\Handling\TranslationHandlerInterface;
use Modera\TranslationsBundle\TokenExtraction\PhpClassTokenExtractor;
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

    private PhpClassTokenExtractor $phpClassTokenExtractor;

    private TranslationReader $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bundle = 'ModeraTranslationsBundle';
        $this->kernel = \Phake::mock(KernelInterface::class);
        \Phake::when($this->kernel)->getBundle($this->bundle)->thenReturn(new DummyBundle());
        $this->extractor = \Phake::mock(ExtractorInterface::class);
        $this->phpClassTokenExtractor = \Phake::mock(PhpClassTokenExtractor::class);
        $this->loader = new TranslationReader();
    }

    public function testTemplateTranslationHandler(): void
    {
        $handler = new TemplateTranslationHandler($this->kernel, $this->loader, $this->extractor, $this->bundle);

        $this->assertInstanceOf(TranslationHandlerInterface::class, $handler);
        $this->assertEquals($this->bundle, $handler->getBundleName());
        $this->assertEquals([TemplateTranslationHandler::SOURCE_NAME], $handler->getSources());
        $this->assertNull($handler->extract(PhpClassesTranslationHandler::SOURCE_NAME, 'en'));
        $catalogue = $handler->extract(TemplateTranslationHandler::SOURCE_NAME, 'en');
        $this->assertInstanceOf(MessageCatalogueInterface::class, $catalogue);
        $this->assertEquals('en', $catalogue->getLocale());
    }

    public function testPhpClassesTranslationHandler(): void
    {
        $handler = new PhpClassesTranslationHandler($this->kernel, $this->loader, $this->phpClassTokenExtractor, $this->bundle);

        $this->assertInstanceOf(TranslationHandlerInterface::class, $handler);
        $this->assertEquals($this->bundle, $handler->getBundleName());
        $this->assertEquals([PhpClassesTranslationHandler::SOURCE_NAME], $handler->getSources());
        $this->assertNull($handler->extract(TemplateTranslationHandler::SOURCE_NAME, 'ru'));
        $catalogue = $handler->extract(PhpClassesTranslationHandler::SOURCE_NAME, 'ru');
        $this->assertInstanceOf(MessageCatalogueInterface::class, $catalogue);
        $this->assertEquals('ru', $catalogue->getLocale());
    }
}

class DummyBundle extends Bundle
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
