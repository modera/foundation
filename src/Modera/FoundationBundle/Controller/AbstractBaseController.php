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

    /**
     * Shortcut access to "security.context" service.
     *
     * @return \Symfony\Component\Security\Core\SecurityContext
     *
     * @deprecated Deprecated since version 1.1, to be removed in 2.0
     */
    protected function sc()
    {
        /* @var DeprecationNoticeEmitter $emitter */
        $emitter = $this->get('modera_foundation.utils.deprecation_notice_emitter');

        $emitter->emit(sprintf('Method %s is deprecated and will be removed in 3.0.', __METHOD__));

        return $this->get('security.context');
    }
}
