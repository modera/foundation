<?php

namespace Modera\BackendTranslationsToolBundle\Tests\Unit\PathProvider;

use Modera\BackendTranslationsToolBundle\FileProvider\ExtjsClassesProvider;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */
class ExtjsClassesProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetFiles()
    {
        $provider = new ExtjsClassesProvider();
        $result = $provider->getFiles(__DIR__.'/resources');
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));
    }
}
