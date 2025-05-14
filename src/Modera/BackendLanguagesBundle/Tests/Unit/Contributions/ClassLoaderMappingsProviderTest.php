<?php

namespace Modera\BackendLanguagesBundle\Tests\Unit\Contributions;

use Modera\BackendLanguagesBundle\Contributions\ClassLoaderMappingsProvider;

class ClassLoaderMappingsProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetItems(): void
    {
        $provider = new ClassLoaderMappingsProvider();

        $items = $provider->getItems();

        $this->assertTrue(\is_array($items));
        $this->assertEquals(1, \count($items));
        $this->assertArrayHasKey('Modera.backend.languages', $items);
        $this->assertEquals('/bundles/moderabackendlanguages/js', $items['Modera.backend.languages']);
    }
}
