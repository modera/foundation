<?php

namespace Modera\BackendTranslationsToolBundle\Tests\Unit\Extractor;

use Modera\BackendTranslationsToolBundle\Extractor\ExtjsClassesExtractor;
use Modera\BackendTranslationsToolBundle\FileProvider\ExtjsClassesProvider;
use Modera\BackendTranslationsToolBundle\FileProvider\FileProviderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class ExtjsClassesExtractorTest extends \PHPUnit\Framework\TestCase
{
    public function testExtract(): void
    {
        $provider = $this->createMock(FileProviderInterface::class);
        $provider->expects($this->any())
                 ->method('getFiles')
                 ->will($this->returnValue([__DIR__.'/resources/class1.js']));

        $catalogue = new MessageCatalogue('en');

        $extractor = new ExtjsClassesExtractor($provider);
        $extractor->setPrefix('**');
        $extractor->extract(__DIR__.'/resources', $catalogue);

        $tokens = $catalogue->all('extjs');
        $this->assertTrue(is_array($tokens));
        $this->assertEquals(3, \count($tokens));
        $this->assetToken('Company.foo.bar.MyClass.firstname', '**Firstname', $tokens);
        $this->assetToken('Company.foo.bar.MyClass.lastname', '**Lastname', $tokens);
        $this->assetToken('Company.foo.bar.MyClass.options', '**Options:', $tokens);
    }

    public function testConstruct(): void
    {
        $extractor = new ExtjsClassesExtractor();
        $this->assertInstanceOf(ExtjsClassesProvider::class, $extractor->getPathProvider());
    }

    private function assetToken($name, $value, array $tokens): void
    {
        $this->assertArrayHasKey($name, $tokens);
        $this->assertEquals($value, $tokens[$name]);
    }
}
