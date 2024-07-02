<?php

namespace Modera\BackendToolsSettingsBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface
{
    private SectionsConfigMerger $merger;

    public function __construct(SectionsConfigMerger $merger)
    {
        $this->merger = $merger;
    }

    public function getItems(): array
    {
        return [
            $this->merger,
        ];
    }
}
