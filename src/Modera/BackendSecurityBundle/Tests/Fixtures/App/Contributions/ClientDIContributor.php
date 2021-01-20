<?php


namespace Modera\BackendSecurityBundle\Tests\Fixtures\App\Contributions;


use Modera\BackendSecurityBundle\Section\Section;
use Sli\ExpanderBundle\Ext\ContributorInterface;

class ClientDIContributor implements ContributorInterface
{
    public function getItems()
    {
        return [
            new Section('section1', 'Section 1', 'icon-1', 'Some.ui.class')
        ];
    }

}
