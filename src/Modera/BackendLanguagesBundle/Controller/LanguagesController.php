<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Modera\BackendConfigUtilsBundle\ModeraBackendConfigUtilsBundle;
use Modera\LanguagesBundle\Entity\Language;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2018 Modera Foundation
 */
#[AsController]
class LanguagesController extends AbstractCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getConfig(): array
    {
        return [
            'entity' => Language::class,
            'security' => [
                'role' => ModeraMJRSecurityIntegrationBundle::ROLE_BACKEND_USER,
                'actions' => [
                    'create' => ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS,
                    'update' => ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS,
                    'remove' => ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS,
                    'batchUpdate' => ModeraBackendConfigUtilsBundle::ROLE_ACCESS_BACKEND_SYSTEM_SETTINGS,
                ],
            ],
            'hydration' => [
                'groups' => [
                    'list' => function (Language $entity) {
                        return [
                            'id' => $entity->getId(),
                            'name' => $entity->getName($this->getDisplayLocale()),
                            'locale' => $entity->getLocale(),
                            'isEnabled' => $entity->isEnabled(),
                            'isDefault' => $entity->isDefault(),
                        ];
                    },
                    'remove' => function (Language $entity) {
                        return [
                            'key' => $entity->getLocale(),
                        ];
                    },
                ],
                'profiles' => [
                    'list', 'remove',
                ],
            ],
        ];
    }

    private function getDisplayLocale(): string
    {
        return $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';
    }
}
