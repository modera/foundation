<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\SecurityBundle\Entity\User;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filter;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filters;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UserSettingsController extends AbstractCrudController
{
    public function getConfig(): array
    {
        return [
            'entity' => UserSettings::class,
            'security' => [
                'actions' => [
                    'create' => function (AuthorizationCheckerInterface $ac, array $params) {
                        if ($ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)) {
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
                        if ($ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)) {
                            return true;
                        } elseif (isset($params['record']['id'])) {
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
                                    && null !== $userSettings->getUser()
                                    && $user->getId() === $userSettings->getUser()->getId()
                                ;
                            }
                        }

                        return false;
                    },
                    'batchUpdate' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES,
                    'remove' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES,
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

                        return $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES);
                    },
                    'list' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                ],
            ],
            'hydration' => [
                'groups' => [
                    'main-form' => function (UserSettings $settings) {
                        return [
                            'id' => $settings->getId(),
                            'username' => $settings->getUser() ? $settings->getUser()->getUsername() : '',
                            'language' => $settings->getLanguage() ? $settings->getLanguage()->getId() : null,
                        ];
                    },
                ],
                'profiles' => [
                    'main-form',
                ],
            ],
        ];
    }

    /**
     * @Remote
     *
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    public function getOrCreateAction(array $params): array
    {
        $response = [
            'success' => false,
        ];
        try {
            $response = $this->getAction($params);
        } catch (\Exception $e) {
        }

        if (isset($response['success']) && $response['success']) {
            return $response;
        }

        return $this->createAction($params);
    }
}
