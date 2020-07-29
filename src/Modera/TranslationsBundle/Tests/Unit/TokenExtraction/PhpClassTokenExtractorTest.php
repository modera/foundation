<?php

namespace Modera\TranslationsBundle\Tests\Unit\TokenExtraction;

use Modera\TranslationsBundle\TokenExtraction\PhpClassTokenExtractor;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PhpClassTokenExtractorTest extends \PHPUnit\Framework\TestCase
{
    public function testExtract()
    {
        $extractor = new PhpClassTokenExtractor();

        $catalogue = new MessageCatalogue('en');
        $extractor->extract(__DIR__.'/dummy-classes', $catalogue);

        $expectedDomains = array(
            'messages', 'foodomain',
            'bardomain', 'tcdomain',
            'Error! Token value can be either a literal string or variable reference.',
        );
        sort($expectedDomains);
        $actualDomains = $catalogue->getDomains();
        sort($actualDomains);

        $this->assertSame($expectedDomains, $actualDomains);

        $this->assertTrue($catalogue->has('hello world', 'messages'));
        $this->assertTrue($catalogue->has('Some simple token', 'messages'));
        $this->assertTrue($catalogue->has('We got something for ya, %s!', 'foodomain'));
        $this->assertTrue($catalogue->has('Another token', 'bardomain'));
        $this->assertTrue($catalogue->has('This is "transChoice token"', 'tcdomain'));
        $this->assertTrue($catalogue->has('trans "implode" to variable', 'bardomain'));
        $this->assertTrue($catalogue->has('transChoice "implode" to variable', 'bardomain'));
        $this->assertTrue($catalogue->has('trans' . PHP_EOL . '"implode"', 'bardomain'));
        $this->assertTrue($catalogue->has('transChoice' . PHP_EOL . '"implode"', 'bardomain'));

        // ---

        $extractor->setPrefix('foo: ');
        $extractor->extract(__DIR__.'/dummy-classes', $catalogue);

        $this->assertTrue($catalogue->has('foo: hello world', 'messages'));
        $this->assertTrue($catalogue->has('foo: Some simple token', 'messages'));
        $this->assertTrue($catalogue->has('foo: We got something for ya, %s!', 'foodomain'));
        $this->assertTrue($catalogue->has('foo: Another token', 'bardomain'));
        $this->assertTrue($catalogue->has('foo: This is "transChoice token"', 'tcdomain'));
        $this->assertTrue($catalogue->has('foo: trans "implode" to variable', 'bardomain'));
        $this->assertTrue($catalogue->has('foo: transChoice "implode" to variable', 'bardomain'));
        $this->assertTrue($catalogue->has('foo: trans' . PHP_EOL . '"implode"', 'bardomain'));
        $this->assertTrue($catalogue->has('foo: transChoice' . PHP_EOL . '"implode"', 'bardomain'));
    }

    public function testExtractMustNotParseFilesWithInvalidUseStmt()
    {
        $extractor = new PhpClassTokenExtractor();

        $catalogue = new MessageCatalogue('en');
        $extractor->extract(__DIR__.'/dummy-files', $catalogue);

        $this->assertEquals(0, count($catalogue->all()));
    }
}
