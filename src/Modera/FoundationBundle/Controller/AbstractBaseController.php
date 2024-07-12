<?php

namespace Modera\FoundationBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;

/**
 * @deprecated Use Symfony\Bundle\FrameworkBundle\Controller\AbstractController instead
 */
class AbstractBaseController extends Controller
{
    /**
     * @deprecated Inject an instance of ManagerRegistry in your controller instead
     */
    protected function em(): EntityManagerInterface
    {
        @\trigger_error(\sprintf(
            'The "%s()" method is deprecated, inject an instance of ManagerRegistry in your controller instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em;
    }
}
