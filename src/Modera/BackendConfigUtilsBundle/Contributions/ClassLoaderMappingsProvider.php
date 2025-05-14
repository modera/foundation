<?php

namespace Modera\BackendConfigUtilsBundle\Contributions;

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
    private ?array $items = null;

    public function getItems(): array
    {
        if (!$this->items) {
            $this->items = [
                'Modera.backend.configutils' => '/bundles/moderabackendconfigutils/js',
            ];
        }

        return $this->items;
    }
}
