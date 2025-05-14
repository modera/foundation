<?php

namespace Modera\BackendConfigUtilsBundle\Tests\Unit\Contributions;

use Modera\BackendConfigUtilsBundle\Contributions\ClassLoaderMappingsProvider;

class ClassLoaderMappingsProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetItems(): void
    {
        $provider = new ClassLoaderMappingsProvider();

        $items = $provider->getItems();

        $this->assertTrue(\is_array($items));
        $this->assertEquals(1, \count($items));
        $this->assertArrayHasKey('Modera.backend.configutils', $items);
        $this->assertEquals('/bundles/moderabackendconfigutils/js', $items['Modera.backend.configutils']);
    }
}
