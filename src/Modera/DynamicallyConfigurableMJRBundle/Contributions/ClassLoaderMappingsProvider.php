<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
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
