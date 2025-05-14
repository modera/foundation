<?php

namespace Modera\ExpanderBundle\Tests\Fixtures\Bundles\DummyBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

#[AsContributorFor('modera_expander.dummy_resources')]
class DummyResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            'foo_resource',
            'bar_resource',
        ];
    }
}
