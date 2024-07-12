<?php

namespace Modera\BackendDashboardBundle\Controller;

use Modera\BackendDashboardBundle\Entity\UserSettings;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\SecurityBundle\Entity\User;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filter;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filters;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UserSettingsController extends AbstractSettingsController
{
    protected function getEntityClass(): string
    {
        return UserSettings::class;
    }

    public function getConfig(): array
    {
        $config = parent::getConfig();

        $config['security'] = [
            'actions' => [
                'create' => function (AuthorizationCheckerInterface $ac, array $params) {
                    if (
                        $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                        || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                    ) {
                        return true;
                    } else {
                        // irrespectively of what privileges user has we will always allow him to create his
                        // own profile data
                        return
                            isset($params['record']['user'])
                            && ($user = $this->getUser()) instanceof User
                            && $user->getId() === $params['record']['user']
                        ;
                    }
                },
                'update' => function (AuthorizationCheckerInterface $ac, array $params) {
                    if (
                        $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                        || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                    ) {
                        return true;
                    } elseif (isset($params['record']) && isset($params['record']['id'])) {
                        /** @var UserSettings[] $entities */
                        $entities = $this->getPersistenceHandler()->query(UserSettings::class, [
                            'filter' => [
                                [
                                    'property' => 'id',
                                    'value' => 'eq:'.$params['record']['id'],
                                ],
                            ],
                        ]);
                        if (\count($entities)) {
                            $userSettings = $entities[0];

                            // irrespectively of what privileges user has we will always allow him to edit his
                            // own profile data
                            return
                                ($user = $this->getUser()) instanceof User
                                && $userSettings->getUser()
                                && $user->getId() == $userSettings->getUser()->getId()
                            ;
                        }
                    }

                    return false;
                },
                'batchUpdate' => function (AuthorizationCheckerInterface $ac, array $params) {
                    return
                        $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                        || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                    ;
                },
                'remove' => function (AuthorizationCheckerInterface $ac, array $params) {
                    return
                        $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                        || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                    ;
                },
                'get' => function (AuthorizationCheckerInterface $ac, array $params) {
                    $userId = null;
                    if (isset($params['filter'])) {
                        foreach (new Filters($params['filter']) as $filter) {
                            /** @var Filter $filter */
                            if ('user.id' === $filter->getProperty() && Filter::COMPARATOR_EQUAL === $filter->getComparator()) {
                                $userId = $filter->getValue();
                            }
                        }
                    }

                    // editing own profile
                    if (null !== $userId) {
                        if (($user = $this->getUser()) instanceof User && $user->getId() == $userId) {
                            return true;
                        }
                    }

                    return
                        $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                        || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                    ;
                },
                'list' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
            ],
        ];

        return $config;
    }
}
