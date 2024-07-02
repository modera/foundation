<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
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
                'Modera.backend.languages' => '/bundles/moderabackendlanguages/js',
            ];
        }

        return $this->items;
    }
}
