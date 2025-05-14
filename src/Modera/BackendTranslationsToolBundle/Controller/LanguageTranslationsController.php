<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Modera\BackendTranslationsToolBundle\Cache\CompileNeeded;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class LanguageTranslationsController extends AbstractCrudController
{
    public function __construct(
        private readonly CompileNeeded $compileNeeded,
    ) {
    }

    public function getConfig(): array
    {
        return [
            'entity' => LanguageTranslationToken::class,
            'security' => [
                'role' => ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION,
            ],
            'hydration' => [
                'groups' => [
                    'main-form' => function (LanguageTranslationToken $ltt) {
                        return [
                            'id' => $ltt->getId(),
                            'translation' => $ltt->getTranslation(),
                            'languageName' => $ltt->getLanguage()?->getName(),
                            'domainName' => $ltt->getTranslationToken()?->getDomain(),
                            'tokenName' => $ltt->getTranslationToken()?->getTokenName(),
                        ];
                    },
                ],
                'profiles' => [
                    'main-form',
                ],
            ],
            'map_data_on_update' => function (array $params, LanguageTranslationToken $entity, DataMapperInterface $defaultMapper) {
                $defaultMapper->mapData($params, $entity);
                $this->compileNeeded->set(true);
            },
        ];
    }
}
