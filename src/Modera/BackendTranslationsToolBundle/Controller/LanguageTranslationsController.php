<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\BackendTranslationsToolBundle\Cache\CompileNeeded;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class LanguageTranslationsController extends AbstractCrudController
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return array(
            'entity' => LanguageTranslationToken::class,
            'security' => array(
                'role' => ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION,
            ),
            'hydration' => array(
                'groups' => array(
                    'main-form' => function (LanguageTranslationToken $ltt) {
                        return array(
                            'id' => $ltt->getId(),
                            'translation' => $ltt->getTranslation(),
                            'languageName' => $ltt->getLanguage()->getName(),
                            'domainName' => $ltt->getTranslationToken()->getDomain(),
                            'tokenName' => $ltt->getTranslationToken()->getTokenName(),
                        );
                    },
                ),
                'profiles' => array(
                    'main-form',
                ),
            ),
            'map_data_on_update' => function (array $params, LanguageTranslationToken $entity, DataMapperInterface $defaultMapper, ContainerInterface $container) {
                $defaultMapper->mapData($params, $entity);

                /* @var CompileNeeded $compileNeeded */
                $compileNeeded = $container->get('modera_backend_translations_tool.cache.compile_needed');
                $compileNeeded->set(true);
            },
        );
    }
}
