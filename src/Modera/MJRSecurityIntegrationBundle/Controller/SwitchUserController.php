<?php

namespace Modera\MJRSecurityIntegrationBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Modera\FoundationBundle\Controller\AbstractBaseController as Controller;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Modera\SecurityBundle\ModeraSecurityBundle;
use Modera\DirectBundle\Annotation\Remote;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class SwitchUserController extends Controller
{
    use BackendUsersTrait;

    /**
     * @Remote
     *
     * @param array $params
     * @return array
     */
    public function listAction(array $params)
    {
        $role = ModeraSecurityBundle::ROLE_ROOT_USER;
        if ($switchUserConfig = $this->getParameter(ModeraSecurityExtension::CONFIG_KEY . '.switch_user')) {
            $role = $switchUserConfig['role'];
        }
        $this->denyAccessUnlessGranted($role);

        $query = $this->createQuery($params);
        $query->setHydrationMode($query::HYDRATE_ARRAY);
        $paginator = new Paginator($query);

        $items = array();
        $total = $paginator->count();
        if ($total) {
            foreach ($paginator as $item) {
                $items[] = $item;
            }
        }

        return array(
            'success' => true,
            'items' => $items,
            'total' => $total,
        );
    }
}
