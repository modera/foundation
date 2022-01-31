<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Sli\ExtJsIntegrationBundle\QueryBuilder\Parsing\Filter;
use Sli\ExtJsIntegrationBundle\QueryBuilder\Parsing\Filters;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UserSettingsController extends AbstractCrudController
{
    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        $self = $this;

        return array(
            'entity' => UserSettings::class,
            'security' => array(
                'actions' => array(
                    'create' => function (AuthorizationCheckerInterface $ac, array $params) use ($self) {
                        if ($ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)) {
                            return true;
                        } else {
                            // irrespectively of what privileges user has we will always allow him to create his
                            // own profile data
                            return (
                                isset($params['record']['user'])
                                && ($user = $this->getUser()) instanceof User
                                && $user->getId() == $params['record']['user']
                            );
                        }
                    },
                    'update' => function (AuthorizationCheckerInterface $ac, array $params) use ($self) {
                        if ($ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)) {
                            return true;
                        } else if (isset($params['record']['id'])) {
                            $entities = $this->getPersistenceHandler()->query(UserSettings::class, array(
                                'filter' => array(
                                    array(
                                        'property' => 'id',
                                        'value' => 'eq:' . $params['record']['id'],
                                    ),
                                ),
                            ));
                            if (count($entities)) {
                                /* @var UserSettings $userSettings */
                                $userSettings = $entities[0];
                                // irrespectively of what privileges user has we will always allow him to edit his
                                // own profile data
                                return (
                                    ($user = $this->getUser()) instanceof User
                                    && $user->getId() == $userSettings->getUser()->getId()
                                );
                            }
                        }

                        return false;
                    },
                    'batchUpdate' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES,
                    'remove' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES,
                    'get' => function(AuthorizationCheckerInterface $ac, array $params) {
                        $userId = null;
                        if (isset($params['filter'])) {
                            foreach (new Filters($params['filter']) as $filter) {
                                /* @var Filter $filter */
                                if ($filter->getProperty() == 'user.id' && $filter->getComparator() == Filter::COMPARATOR_EQUAL) {
                                    $userId = $filter->getValue();
                                }
                            }
                        }

                        // editing own profile
                        if (null !== $userId) {
                            if (($user = $this->getUser()) && $user->getId() == $userId) {
                                return true;
                            }
                        }

                        return $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES);
                    },
                    'list' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                ),
            ),
            'hydration' => array(
                'groups' => array(
                    'main-form' => function (UserSettings $settings) {
                        return array(
                            'id' => $settings->getId(),
                            'username' => $settings->getUser()->getUsername(),
                            'language' => $settings->getLanguage() ? $settings->getLanguage()->getId() : null,
                        );
                    },
                ),
                'profiles' => array(
                    'main-form',
                ),
            ),
        );
    }

    /**
     * @Remote
     */
    public function getOrCreateAction(array $params): array
    {
        $response = array(
            'success' => false,
        );
        try {
            $response = $this->getAction($params);
        } catch (\Exception $e) {}

        if (isset($response['success']) && $response['success']) {
            return $response;
        }

        return $this->createAction($params);
    }
}
