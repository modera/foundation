<?php

namespace Modera\ExpanderBundle\Tests\Unit\Ext;

use Modera\ExpanderBundle\Ext\SimpleContributor;

class DynamicContributorTest extends \PHPUnit\Framework\TestCase
{
    public function testThemAll(): void
    {
        $c1 = new \stdClass();
        $c2 = $c1;

        $dc = new SimpleContributor([$c1, $c2]);
        $this->assertEquals(1, \count($dc->getItems()));

        $dc->addItem($c1);
        $this->assertEquals(1, \count($dc->getItems()));

        $dc->addItem(new \stdClass());
        $this->assertEquals(2, \count($dc->getItems()));
    }
}
