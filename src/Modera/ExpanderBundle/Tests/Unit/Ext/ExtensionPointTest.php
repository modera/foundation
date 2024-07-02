<?php

namespace Modera\ExpanderBundle\Tests\Unit\Ext;

use Modera\ExpanderBundle\DependencyInjection\CompositeContributorsProviderCompilerPass;
use Modera\ExpanderBundle\Ext\ExtensionPoint;

class ExtensionPointTest extends \PHPUnit\Framework\TestCase
{
    private ExtensionPoint $ep;

    public function setUp(): void
    {
        $this->ep = new ExtensionPoint('foo.blah.cities');
    }

    public function testCreateCompilerPass()
    {
        /** @var CompositeContributorsProviderCompilerPass $cp */
        $cp = $this->ep->createCompilerPass();

        $this->assertInstanceOf(CompositeContributorsProviderCompilerPass::class, $cp);
        $this->assertEquals('foo.blah.cities_provider', $cp->getProviderServiceId());
        $this->assertEquals('foo.blah.cities_provider', $cp->getContributorServiceTagName());
    }

    public function testMethodChaining()
    {
        $result = $this->ep->setDescription('desc');

        $this->assertInstanceOf(ExtensionPoint::class, $result);

        $result = $this->ep->setCategory('cat');

        $this->assertInstanceOf(ExtensionPoint::class, $result);

        $result = $this->ep->setContributionTag('ct');

        $this->assertInstanceOf(ExtensionPoint::class, $result);

        $result = $this->ep->setDetailedDescription('foo');

        $this->assertInstanceOf(ExtensionPoint::class, $result);
    }
}
