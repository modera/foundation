<?php

namespace Modera\BackendLanguagesBundle\Ext;

use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
class LocalesProvider implements ContributorInterface
{
    /**
     * @var string[]
     */
    private $items = array();

    /**
     * @param string[] $items
     */
    public function __construct(array $items = array())
    {
        $this->items = $items;
    }

    /**
     * @return string[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
