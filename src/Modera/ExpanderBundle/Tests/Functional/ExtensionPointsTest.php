<?php

namespace Modera\ExpanderBundle\Tests\Functional;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExtensionPointsTest extends WebTestCase
{
    public function testHowWellItWorks(): void
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();

        /** @var ContributorInterface $provider */
        $provider = $container->get('modera_expander.dummy_resources_provider');

        $this->assertInstanceOf('Modera\ExpanderBundle\Ext\ContributorInterface', $provider);

        $result = $provider->getItems();

        $this->assertTrue(\is_array($result));

        // contributed by \Modera\ExpanderBundle\Tests\Fixtures\Bundles\DummyBundle\Contributions\DummyResourcesProvider
        $this->assertTrue(\in_array('foo_resource', $result));
        $this->assertTrue(\in_array('bar_resource', $result));

        // contributed indirectly by \Modera\ExpanderBundle\Tests\Fixtures\Bundles\DummyBundle\ModeraExpanderDummyBundle::getExtensionPointContributions
        $this->assertTrue(\in_array('baz_resource', $result));
    }
}
