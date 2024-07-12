<?php

namespace Modera\MJRSecurityIntegrationBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Modera\DirectBundle\Annotation\Remote;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Modera\SecurityBundle\ModeraSecurityBundle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class SwitchUserController extends Controller
{
    use BackendUsersTrait;

    protected function getContainer(): ContainerInterface
    {
        /** @var ContainerInterface $container */
        $container = $this->container;

        return $container;
    }

    /**
     * @Remote
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function listAction(array $params): array
    {
        $role = ModeraSecurityBundle::ROLE_ROOT_USER;

        /** @var ?array{'role': string} $switchUserConfig */
        $switchUserConfig = $this->getContainer()->getParameter(ModeraSecurityExtension::CONFIG_KEY.'.switch_user');
        if ($switchUserConfig) {
            $role = $switchUserConfig['role'];
        }
        $this->denyAccessUnlessGranted($role);

        $query = $this->createQuery($params);
        $query->setHydrationMode($query::HYDRATE_ARRAY);
        $paginator = new Paginator($query);

        $items = [];
        $total = $paginator->count();
        if ($total) {
            foreach ($paginator as $item) {
                $items[] = $item;
            }
        }

        return [
            'success' => true,
            'items' => $items,
            'total' => $total,
        ];
    }
}
