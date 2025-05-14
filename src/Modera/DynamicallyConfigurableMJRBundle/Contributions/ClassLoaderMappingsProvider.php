<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.class_loader_mappings')]
class ClassLoaderMappingsProvider implements ContributorInterface
{
    /**
     * @var string[]
     */
    private array $items;

    public function __construct()
    {
        $this->items = [
            'Modera.backend.dcmjr' => '/bundles/moderadynamicallyconfigurablemjr/js',
        ];
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
