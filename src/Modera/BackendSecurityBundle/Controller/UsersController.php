<?php

namespace Modera\BackendSecurityBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Sli\ExtJsIntegrationBundle\QueryBuilder\Parsing\Filter;
use Sli\ExtJsIntegrationBundle\QueryBuilder\Parsing\Filters;
use Modera\FoundationBundle\Translation\T;
use Modera\ServerCrudBundle\Persistence\PersistenceHandlerInterface;
use Modera\ServerCrudBundle\Validation\EntityValidatorInterface;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\ServerCrudBundle\Validation\ValidationResult;
use Modera\ServerCrudBundle\Persistence\OperationResult;
use Modera\ServerCrudBundle\Hydration\HydrationProfile;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\SecurityBundle\PasswordStrength\BadPasswordException;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Modera\SecurityBundle\ModeraSecurityBundle;
use Modera\SecurityBundle\Service\UserService;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UsersController extends AbstractCrudController
{
    public function getConfig(): array
    {
        return array(
            'entity' => User::class,
            'security' => array(
                'actions' => array(
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
                            return (
                                isset($params['record']['id'])
                                && ($user = $this->getUser()) instanceof User
                                && $user->getId() == $params['record']['id']
                            );
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
                    'get' => function(AuthorizationCheckerInterface $ac, array $params) {
                        $userId = null;
                        if (isset($params['filter'])) {
                            foreach (new Filters($params['filter']) as $filter) {
                                /* @var Filter $filter */
                                if ($filter->getProperty() == 'id' && $filter->getComparator() == Filter::COMPARATOR_EQUAL) {
                                    $userId = $filter->getValue();
                                }
                            }
                        }

                        // editing own profile
                        if (null !== $userId) {
                            if (($loggedInUser = $this->getUser()) && $loggedInUser->getId() == $userId) {
                                return true;
                            }
                        }

                        return (
                            $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_ACCOUNTS)
                            || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)
                            || $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                        );
                    },
                    'list' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                ),
            ),
            'hydration' => array(
                'groups' => array(
                    'main-form' => function (User $user) {
                        $meta = array();
                        if (
                            $this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES) ||
                            $this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                        ) {
                            $meta = $user->getMeta();
                        }

                        return array(
                            'id' => $user->getId(),
                            'email' => $user->getEmail(),
                            'username' => $user->getUsername(),
                            'personalId' => $user->getPersonalId(),
                            'firstName' => $user->getFirstName(),
                            'lastName' => $user->getLastName(),
                            'middleName' => $user->getMiddleName(),
                            'meta' => $meta,
                        );
                    },
                    'list' => function (User $user) {
                        $groups = array();
                        foreach ($user->getGroups() as $group) {
                            $groups[] = $group->getName();
                        }

                        $permissions = array();
                        foreach ($user->getPermissions() as $permission) {
                            $permissions[] = $permission->getName();
                        }

                        $meta = array();
                        if (
                            $this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES) ||
                            $this->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILE_INFORMATION)
                        ) {
                            $meta = $user->getMeta();
                        }

                        return array(
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
                        );
                    },
                    'compact-list' => function (User $user) {
                        $groups = array();
                        foreach ($user->getGroups() as $group) {
                            $groups[] = $group->getId();
                        }

                        $permissions = array();
                        foreach ($user->getPermissions() as $permission) {
                            $permissions[] = $permission->getId();
                        }

                        return array(
                            'id' => $user->getId(),
                            'username' => $user->getUsername(),
                            'fullname' => $user->getFullName(),
                            'isActive' => $user->isActive(),
                            'state' => $user->getState(),
                            'groups' => $groups,
                            'permissions' => $permissions,
                        );
                    },
                    'delete-user' => ['username'],
                ),
                'profiles' => array(
                    'list',
                    'delete-user',
                    'main-form',
                    'compact-list',
                    'modera-backend-security-group-groupusers' => HydrationProfile::create(false)->useGroups(array('compact-list')),
                ),
            ),
            'map_data_on_create' => function (array $params, User $user, DataMapperInterface $defaultMapper, ContainerInterface $container) {
                $defaultMapper->mapData($params, $user);

                if (isset($params['plainPassword']) && $params['plainPassword']) {
                    $plainPassword = $params['plainPassword'];
                } else {
                    $plainPassword = $this->getPasswordManager()->generatePassword();
                }

                try {
                    if (isset($params['sendPassword']) && $params['sendPassword'] != '') {
                        $this->getPasswordManager()->encodeAndSetPasswordAndThenEmailIt($user, $plainPassword);
                    } else {
                        $this->getPasswordManager()->encodeAndSetPassword($user, $plainPassword);
                    }
                } catch (BadPasswordException $e) {
                    throw new BadPasswordException($e->getErrors()[0], null, $e);
                }
            },
            'map_data_on_update' => function (array $params, User $user, DataMapperInterface $defaultMapper, ContainerInterface $container) {
                $ignoreMapping = [ 'active', 'plainPassword', 'sendPassword' ];
                $params = \array_intersect_key($params, \array_flip($this->getAllowedFieldsToEdit($user)));
                $params = \array_diff_key($params, \array_flip($ignoreMapping));
                $defaultMapper->mapData($params, $user);
            },
            'update_entity_handler' => function (User $user, array $params, PersistenceHandlerInterface $defaultHandler, ContainerInterface $container) {
                $params = $params['record'];

                if (isset($params['active'])) {
                    if ($params['active']) {
                        $this->getUserService()->enable($user);
                        $activityMsg = T::trans('Profile enabled for user "%user%".', array('%user%' => $user->getUsername()));
                        $activityContext = array(
                            'type' => 'user.profile_enabled',
                            'author' => $this->getUser()->getId(),
                        );
                    } else {
                        $this->getUserService()->disable($user);
                        $activityMsg = T::trans('Profile disabled for user "%user%".', array('%user%' => $user->getUsername()));
                        $activityContext = array(
                            'type' => 'user.profile_disabled',
                            'author' => $this->getUser()->getId(),
                        );
                    }
                    $this->getActivityManager()->info($activityMsg, $activityContext);
                } else if (isset($params['plainPassword']) && $params['plainPassword']) {
                    // Password encoding and setting is done in "updated_entity_validator"
                    $activityMsg = T::trans('Password has been changed for user "%user%".', array('%user%' => $user->getUsername()));
                    $activityContext = array(
                        'type' => 'user.password_changed',
                        'author' => $this->getUser()->getId(),
                    );
                    $this->getActivityManager()->info($activityMsg, $activityContext);
                } else {
                    $activityMsg = T::trans('Profile data is changed for user "%user%".', array('%user%' => $user->getUsername()));
                    $activityContext = array(
                        'type' => 'user.profile_updated',
                        'author' => $this->getUser()->getId(),
                    );
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
                        if (($loggedInUser = $this->getUser()) && $loggedInUser->getId() !== $user->getId() && $this->getUserService()->isRootUser($user)) {
                            $message = T::trans('Unable to change password for ROOT user.');
                            $e = new BadPasswordException($message);
                            $e->setErrors([$message]);
                            throw $e;
                        }

                        if (isset($params['sendPassword']) && $params['sendPassword'] != '') {
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

                foreach ($entities as $entity) {
                    /* @var User $entity*/
                    $this->getUserService()->remove($entity);
                    $operationResult->reportEntity(User::class, $entity->getId(), OperationResult::TYPE_ENTITY_REMOVED);
                }

                return $operationResult;
            },
        );
    }

    /**
     * @Remote
     */
    public function generatePasswordAction(array $params): array
    {
        /* @var User $authenticatedUser */
        $authenticatedUser = $this->getUser();

        $targetUser = null;
        if (isset($params['userId'])) {
            /* @var User $requestedUser */
            $requestedUser = $this
                ->getDoctrine()
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

        return array(
            'success' => true,
            'result' => array(
                'plainPassword' => $this->getPasswordManager()->generatePassword($targetUser),
            ),
        );
    }

    /**
     * @Remote
     */
    public function isPasswordRotationNeededAction(array $params): array
    {
        $isRotationNeeded = false;
        if (!$this->isGranted('ROLE_PREVIOUS_ADMIN') && !$this->isGranted(ModeraSecurityBundle::ROLE_ROOT_USER)) {
            $isRotationNeeded = $this->getPasswordManager()->isItTimeToRotatePassword($this->getUser());
        }

        return array(
            'success' => true,
            'result' => array(
                'isRotationNeeded' => $isRotationNeeded,
            ),
        );
    }

    private function getPasswordManager(): PasswordManager
    {
        return $this->get('modera_security.password_strength.password_manager');
    }

    private function getUserService(): UserService
    {
        return $this->get('modera_security.service.user_service');
    }

    private function getActivityManager(): LoggerInterface
    {
        return $this->get('modera_activity_logger.manager.activity_manager');
    }

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
        } else if (($loggedInUser = $this->getUser()) && $loggedInUser->getId() === $user->getId()) {
            $allowedFields = array_merge($allowedFields, [
                'plainPassword',
            ]);
        }

        return $allowedFields;
    }
}
