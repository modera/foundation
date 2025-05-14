<?php

namespace Modera\SecurityBundle\RootUserHandling;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\Entity\UserInterface;
use Modera\SecurityBundle\ModeraSecurityBundle;

/**
 * This implementation will use semantic bundle configuration to retrieve information about root user.
 *
 * @see \Modera\SecurityBundle\DependencyInjection\Configuration
 *
 * @copyright 2014 Modera Foundation
 */
class SemanticConfigRootUserHandler implements RootUserHandlerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * @param array{
     *     'root_user': array<string, mixed>,
     *     'switch_user'?: array{'role': string},
     *  } $bundleConfig
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        array $bundleConfig,
    ) {
        $this->config = $bundleConfig['root_user'];

        $this->config['switch_user_role'] = null;
        if (\is_array($bundleConfig['switch_user'] ?? null)) {
            $this->config['switch_user_role'] = $bundleConfig['switch_user']['role'];
        }
    }

    public function isRootUser(UserInterface $user): bool
    {
        return $this->getUser()->isEqualTo($user);
    }

    public function getUser(): UserInterface
    {
        /** @var array<string, mixed> $query */
        $query = $this->config['query'];

        /** @var ?UserInterface $rootUser */
        $rootUser = $this->em->getRepository(User::class)->findOneBy($query);

        if (!$rootUser) {
            throw new RootUserNotFoundException('Unable to find root user using query: '.\json_encode($query));
        }

        return $rootUser;
    }

    public function getRoles(): array
    {
        $roles = $this->config['roles'];

        if (\is_string($roles) && '*' === $roles) {
            $query = \sprintf('SELECT e.roleName FROM %s e', Permission::class);
            $query = $this->em->createQuery($query);
            /** @var array{'roleName': string}[] $result */
            $result = $query->getResult(Query::HYDRATE_SCALAR);

            $roleNames = [];
            foreach ($result as $roleName) {
                $roleNames[] = $roleName['roleName'];
            }

            $roles = $roleNames;
        }

        if (!\is_array($roles)) {
            throw new \RuntimeException('Neither "*" nor array is used to define root user roles!');
        }

        if ($this->config['switch_user_role']) {
            $roles[] = $this->config['switch_user_role'];
        }

        $roles[] = ModeraSecurityBundle::ROLE_ROOT_USER;

        return $roles;
    }
}
