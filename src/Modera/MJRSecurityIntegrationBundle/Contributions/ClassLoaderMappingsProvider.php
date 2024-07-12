<?php

namespace Modera\MJRSecurityIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
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
