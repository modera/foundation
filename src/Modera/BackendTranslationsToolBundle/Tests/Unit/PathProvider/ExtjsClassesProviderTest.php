<?php

namespace Modera\BackendTranslationsToolBundle\Tests\Unit\PathProvider;

use Modera\BackendTranslationsToolBundle\FileProvider\ExtjsClassesProvider;

class ExtjsClassesProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetFiles(): void
    {
        $provider = new ExtjsClassesProvider();
        $result = $provider->getFiles(__DIR__.'/resources');
        $this->assertTrue(\is_array($result));
        $this->assertEquals(1, \count($result));
    }
}
