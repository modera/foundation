<?php

namespace Modera\BackendTranslationsToolBundle\Tests\Unit\Handling;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Reader\TranslationReader;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Modera\TranslationsBundle\Handling\TemplateTranslationHandler;
use Modera\TranslationsBundle\Handling\PhpClassesTranslationHandler;
use Modera\BackendTranslationsToolBundle\Handling\ExtjsTranslationHandler;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TranslationHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $bundle;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * @var TranslationReader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();

        $this->bundle = 'BackendTranslationsToolBundle';
        $this->kernel = \Phake::mock('Symfony\Component\HttpKernel\KernelInterface');
        \Phake::when($this->kernel)->getBundle($this->bundle)->thenReturn(new DummyBundle());
        $this->extractor = \Phake::mock('Symfony\Component\Translation\Extractor\ExtractorInterface');
        $this->loader = new TranslationReader();
    }

    public function testExtjsTranslationHandler()
    {
        $handler = new ExtjsTranslationHandler($this->kernel, $this->loader, $this->extractor, $this->bundle);

        $this->assertInstanceOf('Modera\TranslationsBundle\Handling\TranslationHandlerInterface', $handler);
        $this->assertEquals($this->bundle, $handler->getBundleName());
        $this->assertEquals(array(ExtjsTranslationHandler::SOURCE_NAME), $handler->getSources());
        $this->assertNull($handler->extract(TemplateTranslationHandler::SOURCE_NAME, 'en'));
        $this->assertNull($handler->extract(PhpClassesTranslationHandler::SOURCE_NAME, 'en'));
        $catalogue = $handler->extract(ExtjsTranslationHandler::SOURCE_NAME, 'en');
        $this->assertInstanceOf('Symfony\Component\Translation\MessageCatalogueInterface', $catalogue);
        $this->assertEquals('en', $catalogue->getLocale());
    }
}

class DummyBundle extends Bundle
{
    public function getPath()
    {
        return __DIR__;
    }
}
