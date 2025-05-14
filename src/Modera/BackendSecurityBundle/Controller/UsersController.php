<?php

namespace Modera\BackendSecurityBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface;
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
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class UsersController extends AbstractCrudController
{
    public function __construct(
        private readonly ActivityManagerInterface $activityManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordManager $passwordManager,
        private readonly UserService $userService,
    ) {
    }

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
            'map_data_on_create' => function (array $params, User $user, DataMapperInterface $defaultMapper) {
                $defaultMapper->mapData($params, $user);

                if (isset($params['plainPassword']) && $params['plainPassword']) {
                    $plainPassword = $params['plainPassword'];
                } else {
                    $plainPassword = $this->passwordManager->generatePassword();
                }

                try {
                    if (isset($params['sendPassword']) && '' != $params['sendPassword']) {
                        $this->passwordManager->encodeAndSetPasswordAndThenEmailIt($user, $plainPassword);
                    } else {
                        $this->passwordManager->encodeAndSetPassword($user, $plainPassword);
                    }
                } catch (BadPasswordException $e) {
                    throw new BadPasswordException($e->getErrors()[0], 0, $e);
                }
            },
            'map_data_on_update' => function (array $params, User $user, DataMapperInterface $defaultMapper) {
                $ignoreMapping = ['active', 'plainPassword', 'sendPassword'];
                $params = \array_intersect_key($params, \array_flip($this->getAllowedFieldsToEdit($user)));
                $params = \array_diff_key($params, \array_flip($ignoreMapping));
                $defaultMapper->mapData($params, $user);
            },
            'update_entity_handler' => function (User $user, array $params, PersistenceHandlerInterface $defaultHandler) {
                $params = $params['record'];

                if (isset($params['active'])) {
                    if ($params['active']) {
                        $this->userService->enable($user);
                        $activityMsg = T::trans('Profile enabled for user "%user%".', ['%user%' => $user->getUsername()]);
                        $activityContext = [
                            'type' => 'user.profile_enabled',
                            'author' => $this->getUser() instanceof User ? (string) $this->getUser()->getId() : null,
                        ];
                    } else {
                        $this->userService->disable($user);
                        $activityMsg = T::trans('Profile disabled for user "%user%".', ['%user%' => $user->getUsername()]);
                        $activityContext = [
                            'type' => 'user.profile_disabled',
                            'author' => $this->getUser() instanceof User ? (string) $this->getUser()->getId() : null,
                        ];
                    }
                    $this->activityManager->info($activityMsg, $activityContext);
                } elseif (isset($params['plainPassword']) && $params['plainPassword']) {
                    // Password encoding and setting is done in "updated_entity_validator"
                    $activityMsg = T::trans('Password has been changed for user "%user%".', ['%user%' => $user->getUsername()]);
                    $activityContext = [
                        'type' => 'user.password_changed',
                        'author' => $this->getUser() instanceof User ? (string) $this->getUser()->getId() : null,
                    ];
                    $this->activityManager->info($activityMsg, $activityContext);
                } else {
                    $activityMsg = T::trans('Profile data is changed for user "%user%".', ['%user%' => $user->getUsername()]);
                    $activityContext = [
                        'type' => 'user.profile_updated',
                        'author' => $this->getUser() instanceof User ? (string) $this->getUser()->getId() : null,
                    ];
                    $this->activityManager->info($activityMsg, $activityContext);
                }

                return $defaultHandler->update($user);
            },
            'updated_entity_validator' => function (array $params, User $user, EntityValidatorInterface $validator, array $config) {
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
                            && $this->userService->isRootUser($user)
                        ) {
                            $message = T::trans('Unable to change password for ROOT user.');
                            $e = new BadPasswordException($message);
                            $e->setErrors([$message]);
                            throw $e;
                        }

                        if (isset($params['sendPassword']) && '' !== $params['sendPassword']) {
                            $this->passwordManager->encodeAndSetPasswordAndThenEmailIt($user, $params['plainPassword']);
                        } else {
                            $this->passwordManager->encodeAndSetPassword($user, $params['plainPassword']);
                        }
                    } catch (BadPasswordException $e) {
                        $result->addFieldError('plainPassword', $e->getErrors()[0]);
                    }
                }

                return $result;
            },
            'remove_entities_handler' => function ($entities, $params, $defaultHandler) {
                $operationResult = new OperationResult();

                /** @var User[] $entities */
                foreach ($entities as $entity) {
                    $this->userService->remove($entity);
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

        if (isset($params['userId'])) {
            /** @var ?User $requestedUser */
            $requestedUser = $this
                ->entityManager
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
                'plainPassword' => $this->passwordManager->generatePassword($targetUser),
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
            $isRotationNeeded = $this->passwordManager->isItTimeToRotatePassword($user);
        }

        return [
            'success' => true,
            'result' => [
                'isRotationNeeded' => $isRotationNeeded,
            ],
        ];
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
