<?php

namespace Modera\BackendSecurityBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Modera\BackendSecurityBundle\ModeraBackendSecurityBundle;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Entity\Group;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\ServerCrudBundle\NewValuesFactory\NewValuesFactoryInterface;
use Modera\ServerCrudBundle\Validation\EntityValidatorInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class GroupsController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getConfig(): array
    {
        $groupEntityValidator = function (array $params, Group $group, EntityValidatorInterface $defaultValidator, array $config) {
            $validationResult = $defaultValidator->validate($group, $config);

            if (!$group->getRefName()) {
                return $validationResult;
            }

            /** @var Group[] $groupWithSuchRefNameList */
            $groupWithSuchRefNameList = $this->entityManager->getRepository(Group::class)->findByRefName($group->getRefName());

            if (\count($groupWithSuchRefNameList) > 0) {
                $groupWithSuchRefName = $groupWithSuchRefNameList[0];

                if ($groupWithSuchRefName->getId() !== $group->getId()) {
                    $validationResult->addFieldError(
                        'refName',
                        T::trans(
                            'This refName is taken. Consider use \'%groupName%\' group or change current reference name.',
                            ['%groupName%' => $groupWithSuchRefName->getName()]
                        )
                    );
                }
            }

            return $validationResult;
        };

        $mapEntity = function (array $params, Group $group, DataMapperInterface $defaultMapper) {
            $allowedFieldsToEdit = ['name', 'refName', 'permissions'];
            $params = \array_intersect_key($params, \array_flip($allowedFieldsToEdit));
            $defaultMapper->mapData($params, $group);

            /*
             * Because of unique constrain we cannot save '' value as refName.
             * Only one time can, actually. :) So, to allow user use groups without
             * refName we have to set null by force because of ExtJs empty form value
             * is ''.
             */
            $refName = $group->getRefName();
            if (!$refName) {
                $group->setRefName(null);
            } else {
                /*
                 * To help users avoid duplicates group we use normalizing for refName
                 */
                $group->setRefName(Group::normalizeRefName($refName));
            }
        };

        return [
            'entity' => Group::class,
            'security' => [
                'role' => ModeraBackendSecurityBundle::ROLE_ACCESS_BACKEND_TOOLS_SECURITY_SECTION,
                'actions' => [
                    'create' => ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS,
                    'update' => ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS,
                    'remove' => ModeraBackendSecurityBundle::ROLE_MANAGE_PERMISSIONS,
                    'batchUpdate' => false,
                ],
            ],
            'hydration' => [
                'groups' => [
                    'list' => function (Group $group) {
                        return [
                            'id' => $group->getId(),
                            'name' => $group->getName(),
                            'usersCount' => \count($group->getUsers()),
                        ];
                    },
                    'delete-group' => ['name'],
                    'main-form' => ['id', 'name', 'refName'],
                    'compact-list' => ['id', 'name'],
                ],
                'profiles' => [
                    'list', 'compact-list',
                    'delete-group',
                    'edit-group' => ['main-form'],
                ],
            ],
            'format_new_entity_values' => function (array $params, array $config, NewValuesFactoryInterface $defaultImpl) {
                return [
                    'refName' => null,
                ];
            },
            'new_entity_validator' => $groupEntityValidator,
            'updated_entity_validator' => $groupEntityValidator,
            'map_data_on_create' => $mapEntity,
            'map_data_on_update' => $mapEntity,
        ];
    }
}
