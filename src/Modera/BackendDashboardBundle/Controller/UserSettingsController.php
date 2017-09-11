<?php

namespace Modera\BackendDashboardBundle\Controller;

use Modera\SecurityBundle\Entity\User;
use Modera\BackendDashboardBundle\Entity\UserSettings;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Sli\ExtJsIntegrationBundle\QueryBuilder\Parsing\Filters;
use Sli\ExtJsIntegrationBundle\QueryBuilder\Parsing\Filter;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UserSettingsController extends AbstractSettingsController
{
    protected function getEntityClass()
    {
        return UserSettings::clazz();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $self = $this;

        $config = parent::getConfig();

        $config['security'] = array(
            'actions' => array(
                'create' => function (AuthorizationCheckerInterface $ac, array $params) use ($self) {
                    if ($ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)) {
                        return true;
                    } else {
                        /* @var TokenStorageInterface $ts */
                        $ts = $self->get('security.token_storage');
                        /* @var User $user */
                        $user = $ts->getToken()->getUser();
                        // irrespectively of what privileges user has we will always allow him to create his
                        // own profile data
                        return $user instanceof User && isset($params['record']['user'])
                        && $user->getId() == $params['record']['user'];
                    }
                },
                'update' => function (AuthorizationCheckerInterface $ac, array $params) use ($self) {
                    if ($ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES)) {
                        return true;
                    } else if (isset($params['record']['id'])) {
                        $entities = $this->getPersistenceHandler()->query(UserSettings::clazz(), array(
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

                            /* @var TokenStorageInterface $ts */
                            $ts = $self->get('security.token_storage');
                            /* @var User $user */
                            $user = $ts->getToken()->getUser();

                            // irrespectively of what privileges user has we will always allow him to edit his
                            // own profile data
                            return $user instanceof User && $user->getId() == $userSettings->getUser()->getId();
                        }
                    }

                    return false;
                },
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

                    $isPossiblyEditingOwnProfile = null !== $userId;
                    if ($isPossiblyEditingOwnProfile) {
                        /* @var TokenStorageInterface $ts */
                        $ts = $this->get('security.token_storage');
                        /* @var User $user */
                        $user = $ts->getToken()->getUser();

                        if ($user->getId() == $userId) {
                            return true;
                        }
                    }

                    return $ac->isGranted(ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES);
                },
                'list' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                'batchUpdate' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES,
                'remove' => ModeraBackendSecurityBundle::ROLE_MANAGE_USER_PROFILES,
            ),
        );

        return $config;
    }
}
