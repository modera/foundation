<?php

namespace Modera\ExpanderBundle\Tests\Unit\Contributing;

use Modera\ExpanderBundle\Contributing\BundleContributorAdapter;
use Modera\ExpanderBundle\Tests\Unit\FooDummyBundle;

class BundleContributorAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function testGetItems()
    {
        $extensionPointName = 'foo_extension_point';

        $bundleName = 'FooDummyBundle';

        $bundle = new FooDummyBundle();
        $bundle->map[$extensionPointName] = ['foo', 'bar'];

        $kernel = \Phake::mock('Symfony\Component\HttpKernel\KernelInterface');
        \Phake::when($kernel)->getBundle($bundleName)->thenReturn($bundle);

        $a = new BundleContributorAdapter($kernel, $bundleName, $extensionPointName);

        $result = $a->getItems();

        $this->assertTrue(\is_array($result));
        $this->assertSame($result, ['foo', 'bar']);

        // ---

        $a = new BundleContributorAdapter($kernel, $bundleName, $extensionPointName);
        $bundle->map = null;

        $this->assertSame([], $a->getItems());
    }

    public function testGetItemsWithVanillaBundle()
    {
        $this->expectException(\InvalidArgumentException::class);

        $extensionPointName = 'foo_extension_point';

        $bundleName = 'FooDummyBundle';

        $bundle = \Phake::mock('Symfony\Component\HttpKernel\Bundle\BundleInterface');

        $kernel = \Phake::mock('Symfony\Component\HttpKernel\KernelInterface');
        \Phake::when($kernel)->getBundle($bundleName)->thenReturn($bundle);

        $a = new BundleContributorAdapter($kernel, $bundleName, $extensionPointName);

        $result = $a->getItems();
    }
}
