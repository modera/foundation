<?php

namespace Modera\FoundationBundle\Controller;

use Modera\FoundationBundle\Utils\DeprecationNoticeEmitter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller provides a bunch of auxiliary methods.
 *
 * @copyright 2013 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class AbstractBaseController extends Controller
{
    /**
     * Shortcut access to "doctrine.orm.entity_manager" service.
     *
     * @return \Doctrine\ORM\EntityManager $em
     */
    protected function em()
    {
        return $this->get('doctrine.orm.entity_manager');
    }
}
