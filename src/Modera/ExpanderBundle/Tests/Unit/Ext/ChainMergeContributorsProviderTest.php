<?php

namespace Modera\ExpanderBundle\Tests\Unit\Ext;

use Modera\ExpanderBundle\Ext\ChainMergeContributorsProvider;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\ExpanderBundle\Ext\OrderedContributorInterface;

class MockOrderAwareContributor implements OrderedContributorInterface
{
    public array $items = [];
    public ?int $order = null;

    public function __construct(?int $order = null, array $items = [])
    {
        $this->order = $order;
        $this->items = $items;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getOrder(): int
    {
        return $this->order ?? 0;
    }
}

class ChainMergeContributorsProviderTest extends \PHPUnit\Framework\TestCase
{
    private ChainMergeContributorsProvider $p;

    public function setUp(): void
    {
        $this->p = new ChainMergeContributorsProvider();
    }

    public function testAddContributorAndThenGetContributors(): void
    {
        $c1 = $this->createMock(ContributorInterface::class);
        $c2 = $this->createMock(ContributorInterface::class);

        $this->p->addContributor($c1);
        $this->p->addContributor($c2);

        $this->assertSame([$c1, $c2], $this->p->getContributors());
    }

    public function testGetItems(): void
    {
        $c1 = $this->createMock(ContributorInterface::class);
        $c1->expects($this->any())
           ->method('getItems')
           ->will($this->returnValue(['foo1', 'foo2']));

        $c2 = $this->createMock(ContributorInterface::class);
        $c2->expects($this->any())
           ->method('getItems')
           ->will($this->returnValue(['bar1', 'bar2']));

        $this->p->addContributor($c1);
        $this->p->addContributor($c2);

        $result = $this->p->getItems();
        $this->assertTrue(\is_array($result));
    }

    public function testGetItemsWithOrder(): void
    {
        $c1 = new MockOrderAwareContributor(100, ['foo']);

        $c2 = \Phake::mock(ContributorInterface::class);
        \Phake::when($c2)->getItems()->thenReturn(['baz']);

        $c3 = new MockOrderAwareContributor(50, ['bar']);

        $this->p->addContributor($c1);
        $this->p->addContributor($c2);
        $this->p->addContributor($c3);

        $this->assertSame(['bar', 'foo', 'baz'], $this->p->getItems());
    }

    public function testGetItemsWithSameOrder(): void
    {
        $c1 = new MockOrderAwareContributor(1, ['foo']);

        $c2 = new MockOrderAwareContributor(1, ['bar']);

        $c3 = \Phake::mock(ContributorInterface::class);
        \Phake::when($c3)->getItems()->thenReturn(['baz']);

        $this->p->addContributor($c1);
        $this->p->addContributor($c2);
        $this->p->addContributor($c3);

        $this->assertSame(['foo', 'bar', 'baz'], $this->p->getItems());
    }
}
