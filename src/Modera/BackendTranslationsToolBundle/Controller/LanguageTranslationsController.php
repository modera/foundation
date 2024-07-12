<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Modera\BackendTranslationsToolBundle\Cache\CompileNeeded;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class LanguageTranslationsController extends AbstractCrudController
{
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
                            'languageName' => $ltt->getLanguage() ? $ltt->getLanguage()->getName() : null,
                            'domainName' => $ltt->getTranslationToken() ? $ltt->getTranslationToken()->getDomain() : null,
                            'tokenName' => $ltt->getTranslationToken() ? $ltt->getTranslationToken()->getTokenName() : null,
                        ];
                    },
                ],
                'profiles' => [
                    'main-form',
                ],
            ],
            'map_data_on_update' => function (array $params, LanguageTranslationToken $entity, DataMapperInterface $defaultMapper, ContainerInterface $container) {
                $defaultMapper->mapData($params, $entity);

                /** @var CompileNeeded $compileNeeded */
                $compileNeeded = $container->get('modera_backend_translations_tool.cache.compile_needed');
                $compileNeeded->set(true);
            },
        ];
    }
}
