<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.class_loader_mappings')]
class ClassLoaderMappingsProvider implements ContributorInterface
{
    /**
     * @var array<string, string>
     */
    private array $items;

    public function __construct()
    {
        $this->items = [
            'Modera.mjrsecurityintegration' => '/bundles/moderamjrsecurityintegration/js',
        ];
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
