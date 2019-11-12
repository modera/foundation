<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\MjrIntegrationBundle\Sections\Section;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SectionsProvider implements ContributorInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->items = array(
                new Section('tools.security', 'Modera.backend.security.toolscontribution.runtime.Section', array(
                    Section::META_NAMESPACE => 'Modera.backend.security',
                    Section::META_NAMESPACE_PATH => '/bundles/moderabackendsecurity/js',
                )),
            );
        }

        return $this->items;
    }
}
