<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Tests\Unit\Contributions;

use Modera\DynamicallyConfigurableMJRBundle\Contributions\ClassLoaderMappingsProvider;

class ClassLoaderMappingsProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetItems(): void
    {
        $provider = new ClassLoaderMappingsProvider();

        $items = $provider->getItems();

        $this->assertEquals(1, \count($items));
        $this->assertArrayHasKey('Modera.backend.dcmjr', $items);
        $this->assertEquals('/bundles/moderadynamicallyconfigurablemjr/js', $items['Modera.backend.dcmjr']);
    }
}
