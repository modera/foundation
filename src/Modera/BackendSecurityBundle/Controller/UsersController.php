<?php

namespace Modera\BackendSecurityBundle\Controller;

use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\ModeraSecurityBundle;
use Modera\SecurityBundle\PasswordStrength\BadPasswordException;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Modera\SecurityBundle\Service\UserService;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\ServerCrudBundle\Hydration\HydrationProfile;
use Modera\ServerCrudBundle\Persistence\OperationResult;
use Modera\ServerCrudBundle\Persistence\PersistenceHandlerInterface;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filter;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filters;
use Modera\ServerCrudBundle\Validation\EntityValidatorInterface;
use Modera\ServerCrudBundle\Validation\ValidationResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UsersController extends AbstractCrudController
{
    public function getConfig(): array
    {
        return [
            'entity' => User::class,
            'security' => [
                'actions' => [
                    'create' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_ACCOUNTS,
                    'remove' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_ACCOUNTS,
                    'update' => function (AuthorizationCheckerInterface $ac, array $params) {
                        if (isset($params['record']) && isset($params['record']['permissions'])) {
                            if (!$ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS)) {
                                return false;
                            }
                        }

                        if (
                            $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                            || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                        ) {
                            return true;
                        } else {
                            // irrespectively of what privileges user has we will always allow him to edit his
                            // own profile data
                            return
                                isset($params['record']['id'])
                                && ($user = $this->getUser()) instanceof User
                                && $user->getId() == $params['record']['id']
                            ;
                        }
                    },
                    'batchUpdate' => function (AuthorizationCheckerInterface $ac, array $params) {
                        if (isset($params['record']) && isset($params['record']['permissions'])) {
                            if (!$ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS)) {
                                return false;
                            }
                        }

                        if (isset($params['records'])) {
                            foreach ($params['records'] as $record) {
                                if (isset($record['permissions'])) {
                                    if (!$ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS)) {
                                        return false;
                                    }
                                }
                            }
                        }

                        return $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES);
                    },
                    'get' => function (AuthorizationCheckerInterface $ac, array $params) {
                        $userId = null;
                        if (isset($params['filter'])) {
                            foreach (new Filters($params['filter']) as $filter) {
                                /** @var Filter $filter */
                                if ('id' === $filter->getProperty() && Filter::COMPARATOR_EQUAL === $filter->getComparator()) {
                                    $userId = $filter->getValue();
                                }
                            }
                        }

                        // editing own profile
                        if (null !== $userId) {
                            if (($loggedInUser = $this->getUser()) instanceof User && $loggedInUser->getId() == $userId) {
                                return true;
                            }
                        }

                        return
                            $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_ACCOUNTS)
                            || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                            || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                        ;
                    },
                    'list' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                ],
            ],
            'hydration' => [
                'groups' => [
                    'main-form' => function (User $user) {
                        $meta = [];
                        if (
                            $this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                            || $this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                        ) {
                            $meta = $user->getMeta();
                        }

                        return [
                            'id' => $user->getId(),
                            'email' => $user->getEmail(),
                            'username' => $user->getUsername(),
                            'personalId' => $user->getPersonalId(),
                            'firstName' => $user->getFirstName(),
                            'lastName' => $user->getLastName(),
                            'middleName' => $user->getMiddleName(),
                            'meta' => $meta,
                        ];
                    },
                    'list' => function (User $user) {
                        $groups = [];
                        foreach ($user->getGroups() as $group) {
                            $groups[] = $group->getName();
                        }

                        $permissions = [];
                        foreach ($user->getPermissions() as $permission) {
                            $permissions[] = $permission->getName();
                        }

                        $meta = [];
                        if (
                            $this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                            || $this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                        ) {
                            $meta = $user->getMeta();
                        }

                        return [
                            'id' => $user->getId(),
                            'email' => $user->getEmail(),
                            'username' => $user->getUsername(),
                            'personalId' => $user->getPersonalId(),
                            'firstName' => $user->getFirstName(),
                            'lastName' => $user->getLastName(),
                            'middleName' => $user->getMiddleName(),
                            'isActive' => $user->isActive(),
                            'state' => $user->getState(),
                            'lastLogin' => $user->getLastLogin() ? $user->getLastLogin()->format(\DateTime::W3C) : null,
                            'groups' => $groups,
                            'permissions' => $permissions,
                            'meta' => $meta,
                        ];
                    },
                    'compact-list' => function (User $user) {
                        $groups = [];
                        foreach ($user->getGroups() as $group) {
                            $groups[] = $group->getId();
                        }

                        $permissions = [];
                        foreach ($user->getPermissions() as $permission) {
                            $permissions[] = $permission->getId();
                        }

                        return [
                            'id' => $user->getId(),
                            'username' => $user->getUsername(),
                            'fullname' => $user->getFullName(),
                            'isActive' => $user->isActive(),
                            'state' => $user->getState(),
                            'groups' => $groups,
                            'permissions' => $permissions,
                        ];
                    },
                    'delete-user' => ['username'],
                ],
                'profiles' => [
                    'list',
                    'delete-user',
                    'main-form',
                    'compact-list',
                    'modera-backend-security-group-groupusers' => HydrationProfile::create(false)->useGroups(['compact-list']),
                ],
            ],
            'map_data_on_create' => function (array $params, User $user, DataMapperInterface $defaultMapper, ContainerInterface $container) {
                $defaultMapper->mapData($params, $user);

                if (isset($params['plainPassword']) && $params['plainPassword']) {
                    $plainPassword = $params['plainPassword'];
                } else {
                    $plainPassword = $this->getPasswordManager()->generatePassword();
                }

                try {
                    if (isset($params['sendPassword']) && '' != $params['sendPassword']) {
                        $this->getPasswordManager()->encodeAndSetPasswordAndThenEmailIt($user, $plainPassword);
                    } else {
                        $this->getPasswordManager()->encodeAndSetPassword($user, $plainPassword);
                    }
                } catch (BadPasswordException $e) {
                    throw new BadPasswordException($e->getErrors()[0], 0, $e);
                }
            },
            'map_data_on_update' => function (array $params, User $user, DataMapperInterface $defaultMapper, ContainerInterface $container) {
                $ignoreMapping = ['active', 'plainPassword', 'sendPassword'];
                $params = \array_intersect_key($params, \array_flip($this->getAllowedFieldsToEdit($user)));
                $params = \array_diff_key($params, \array_flip($ignoreMapping));
                $defaultMapper->mapData($params, $user);
            },
            'update_entity_handler' => function (User $user, array $params, PersistenceHandlerInterface $defaultHandler, ContainerInterface $container) {
                $params = $params['record'];

                if (isset($params['active'])) {
                    if ($params['active']) {
                        $this->getUserService()->enable($user);
                        $activityMsg = T::trans('Profile enabled for user "%user%".', ['%user%' => $user->getUsername()]);
                        $activityContext = [
                            'type' => 'user.profile_enabled',
                            'author' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                        ];
                    } else {
                        $this->getUserService()->disable($user);
                        $activityMsg = T::trans('Profile disabled for user "%user%".', ['%user%' => $user->getUsername()]);
                        $activityContext = [
                            'type' => 'user.profile_disabled',
                            'author' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                        ];
                    }
                    $this->getActivityManager()->info($activityMsg, $activityContext);
                } elseif (isset($params['plainPassword']) && $params['plainPassword']) {
                    // Password encoding and setting is done in "updated_entity_validator"
                    $activityMsg = T::trans('Password has been changed for user "%user%".', ['%user%' => $user->getUsername()]);
                    $activityContext = [
                        'type' => 'user.password_changed',
                        'author' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                    ];
                    $this->getActivityManager()->info($activityMsg, $activityContext);
                } else {
                    $activityMsg = T::trans('Profile data is changed for user "%user%".', ['%user%' => $user->getUsername()]);
                    $activityContext = [
                        'type' => 'user.profile_updated',
                        'author' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                    ];
                    $this->getActivityManager()->info($activityMsg, $activityContext);
                }

                return $defaultHandler->update($user);
            },
            'updated_entity_validator' => function (array $params, User $user, EntityValidatorInterface $validator, array $config, ContainerInterface $container) {
                if (!isset($params['record'])) {
                    $result = new ValidationResult();
                    $result->addGeneralError('Bad request.');

                    return $result;
                }

                $result = new ValidationResult();
                foreach (\array_diff_key($params['record'], \array_flip($this->getAllowedFieldsToEdit($user))) as $key => $value) {
                    $result->addFieldError($key, 'Access denied.');
                }
                if ($result->hasErrors()) {
                    return $result;
                }

                $result = $validator->validate($user, $config);

                $params = $params['record'];
                if (isset($params['plainPassword']) && $params['plainPassword']) {
                    // We are force to do it here because we have no access to validation in "map_data_on_update"
                    try {
                        if (
                            ($loggedInUser = $this->getUser()) instanceof User
                            && $loggedInUser->getId() !== $user->getId()
                            && $this->getUserService()->isRootUser($user)
                        ) {
                            $message = T::trans('Unable to change password for ROOT user.');
                            $e = new BadPasswordException($message);
                            $e->setErrors([$message]);
                            throw $e;
                        }

                        if (isset($params['sendPassword']) && '' !== $params['sendPassword']) {
                            $this->getPasswordManager()->encodeAndSetPasswordAndThenEmailIt($user, $params['plainPassword']);
                        } else {
                            $this->getPasswordManager()->encodeAndSetPassword($user, $params['plainPassword']);
                        }
                    } catch (BadPasswordException $e) {
                        $result->addFieldError('plainPassword', $e->getErrors()[0]);
                    }
                }

                return $result;
            },
            'remove_entities_handler' => function ($entities, $params, $defaultHandler, ContainerInterface $container) {
                $operationResult = new OperationResult();

                /** @var User[] $entities */
                foreach ($entities as $entity) {
                    $this->getUserService()->remove($entity);
                    $operationResult->reportEntity(
                        User::class,
                        $entity->getId() ?? 0,
                        OperationResult::TYPE_ENTITY_REMOVED
                    );
                }

                return $operationResult;
            },
        ];
    }

    /**
     * @Remote
     *
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    public function generatePasswordAction(array $params): array
    {
        /** @var User $authenticatedUser */
        $authenticatedUser = $this->getUser();

        $targetUser = null;
        if (isset($params['userId'])) {
            /** @var ?User $requestedUser */
            $requestedUser = $this
                ->em()
                ->getRepository(User::class)
                ->find($params['userId'])
            ;

            if ($requestedUser) {
                if (!$authenticatedUser->isEqualTo($requestedUser)) {
                    $this->denyAccessUnlessGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES);
                }

                $targetUser = $requestedUser;
            } else {
                throw $this->createAccessDeniedException();
            }
        } else {
            $targetUser = $authenticatedUser;
        }

        return [
            'success' => true,
            'result' => [
                'plainPassword' => $this->getPasswordManager()->generatePassword($targetUser),
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
    public function isPasswordRotationNeededAction(array $params): array
    {
        $isRotationNeeded = false;
        if (
            ($user = $this->getUser()) instanceof User
            && !$this->isGranted('ROLE_PREVIOUS_ADMIN')
            && !$this->isGranted(ModeraSecurityBundle::ROLE_ROOT_USER)
        ) {
            $isRotationNeeded = $this->getPasswordManager()->isItTimeToRotatePassword($user);
        }

        return [
            'success' => true,
            'result' => [
                'isRotationNeeded' => $isRotationNeeded,
            ],
        ];
    }

    private function getPasswordManager(): PasswordManager
    {
        /** @var PasswordManager $passwordManager */
        $passwordManager = $this->container->get('modera_security.password_strength.password_manager');

        return $passwordManager;
    }

    private function getUserService(): UserService
    {
        /** @var UserService $userService */
        $userService = $this->container->get('modera_security.service.user_service');

        return $userService;
    }

    private function getActivityManager(): LoggerInterface
    {
        /** @var LoggerInterface $activityManager */
        $activityManager = $this->container->get('modera_activity_logger.manager.activity_manager');

        return $activityManager;
    }

    /**
     * @return string[]
     */
    private function getAllowedFieldsToEdit(User $user): array
    {
        $allowedFields = [
            'id',
            'email',
            'personalId',
            'firstName',
            'lastName',
            'middleName',
        ];

        if ($this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS)) {
            $allowedFields = array_merge($allowedFields, [
                'permissions',
            ]);
        }

        if ($this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)) {
            $allowedFields = array_merge($allowedFields, [
                'plainPassword',
                'sendPassword',
                'username',
                'active',
                'groups',
            ]);
        } elseif (
            ($loggedInUser = $this->getUser()) instanceof User
            && $loggedInUser->getId() === $user->getId()
        ) {
            $allowedFields = array_merge($allowedFields, [
                'plainPassword',
            ]);
        }

        return $allowedFields;
    }
}
