<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Modera\DirectBundle\Annotation\Remote;
use Modera\ServerCrudBundle\Exceptions\BadRequestException;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\ExceptionHandling\ExceptionHandlerInterface;
use Modera\TranslationsBundle\Compiler\TranslationsCompiler;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Modera\BackendTranslationsToolBundle\DependencyInjection\ModeraBackendTranslationsToolExtension;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\BackendTranslationsToolBundle\Contributions\FiltersProvider;
use Modera\BackendTranslationsToolBundle\Filtering\FilterInterface;
use Modera\BackendTranslationsToolBundle\Cache\CompileNeeded;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TranslationsController extends AbstractCrudController
{
    private function checkAccess()
    {
        $role = ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION;
        if (false === $this->get('security.authorization_checker')->isGranted($role)) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @param LanguageTranslationToken $ltt
     * @return array
     */
    private function hydrateLanguageTranslationToken(LanguageTranslationToken $ltt)
    {
        return array(
            'id' => $ltt->getId(),
            'isNew' => $ltt->isNew(),
            'translation' => $ltt->getTranslation(),
            'locale' => $ltt->getLanguage()->getLocale(),
            'language' => $ltt->getLanguage()->getName(),
        );
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return array(
            'entity' => TranslationToken::clazz(),
            'security' => array(
                'role' => ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION,
            ),
            'hydration' => array(
                'groups' => array(
                    'list' => function (TranslationToken $translationToken) {
                        $translations = array();
                        foreach ($translationToken->getLanguageTranslationTokens() as $ltt) {
                            if ($ltt->getLanguage()->isEnabled()) {
                                $translations[$ltt->getLanguage()->getId()] = $this->hydrateLanguageTranslationToken($ltt);
                            }
                        }

                        return array(
                            'id' => $translationToken->getId(),
                            'domain' => $translationToken->getDomain(),
                            'tokenName' => $translationToken->getTokenName(),
                            'isObsolete' => $translationToken->isObsolete(),
                            'translations' => $translations,
                        );
                    },
                ),
                'profiles' => array(
                    'list',
                ),
            ),
        );
    }

    /**
     * @Remote
     *
     * @param array $params
     */
    public function listWithFiltersAction(array $params)
    {
        $this->checkAccess();

        try {
            $filterId = null;
            $filterValue = null;
            if (isset($params['filter'])) {
                foreach ($params['filter'] as $filter) {
                    if ('__filter__' == $filter['property']) {
                        $parts = explode('-', $filter['value'], 2);
                        $filterId = $parts[0];
                        if (isset($parts[1])) {
                            $filterValue = $parts[1];
                        }
                        break;
                    }
                }

                if ($filterValue) {
                    $params['filter'] = [
                        array('property' => 'languageTranslationTokens.language.isEnabled', 'value' => 'eq:true'),
                        [
                            array('property' => 'domain', 'value' => 'eq:'.$filterValue),
                            array('property' => 'tokenName', 'value' => 'like:%'.$filterValue.'%'),
                            array('property' => 'languageTranslationTokens.translation', 'value' => 'like:%'.$filterValue.'%'),
                        ],
                    ];
                } else {
                    $params['filter'] = null;
                }
            }

            if (!$filterId) {
                $e = new BadRequestException('"/filter" request parameter is not provided');
                $e->setPath('/');
                $e->setParams($params);

                throw $e;
            }

            /* @var FiltersProvider $filtersProvider */
            $filtersProvider = $this->get('modera_backend_translations_tool.filters_provider');

            $filter = null;
            $filters = $filtersProvider->getItems();
            foreach ($filters['translation_token'] as $iteratedFilter) {
                /* @var FilterInterface $iteratedFilter */

                if ($iteratedFilter->getId() == $filterId && $iteratedFilter->isAllowed()) {
                    $filter = $iteratedFilter;
                    break;
                }
            }

            if (!$filter) {
                throw new \RuntimeException(sprintf('Filter with given parameter "%s" not found', $filterId));
            }

            $result = $filter->getResult($params);

            $hydratedItems = [];
            foreach ($result['items'] as $entity) {
                $hydratedItems[] = $this->hydrate($entity, $params);
            }

            $result['items'] = $hydratedItems;

            return $result;
        } catch (\Exception $e) {
            return $this->createExceptionResponse($e, ExceptionHandlerInterface::OPERATION_LIST);
        }
    }

    /**
     * @Remote
     *
     * @param array $params
     */
    public function importAction(array $params)
    {
        $this->checkAccess();

        $app = new Application($this->get('kernel'));
        $app->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => $this->container->getParameter(ModeraBackendTranslationsToolExtension::CONFIG_KEY . '.import_cmd'),
        ));
        $input->setInteractive(false);

        $result = $app->run($input, new NullOutput());

        return array(
            'success' => (0 === $result),
            'updated_models' => array(
                'modera.translations_bundle.translation_token' => [],
            ),
        );
    }

    /**
     * @Remote
     */
    public function compileAction(array $params)
    {
        $this->checkAccess();

        /* @var TranslationsCompiler $compiler */
        $compiler = $this->get('modera_translations.compiler.translations_compiler');

        $result = $compiler->compile();

        if ($result->isSuccessful()) {
            /* @var CompileNeeded $compileNeeded */
            $compileNeeded = $this->get('modera_backend_translations_tool.cache.compile_needed');
            $compileNeeded->set(false);

        } else {
            /* @var KernelInterface $kernel */
            $kernel = $this->get('kernel');

            // if activity logger bundle is available then logging the error there as well
            $bundles = $kernel->getBundles();
            if (isset($bundles['ModeraActivityLoggerBundle'])) {
                /* @var UserInterface $user*/
                $user = $this->getUser();

                /* @var LoggerInterface $logger */
                $logger = $this->get('modera_activity_logger.manager.activity_manager');

                $logger->error(
                    // 'message' field for Activity entity is mapped as "string", so we can't put there a whole message
                    "Failed to compile translations, details: \n\n".substr($result->getErrorMessage(), 0, 150).'...',
                    array(
                        'type' => 'translations',
                        'author' => $user->getUsername(),
                    )
                );
            }
        }

        // will be handled by MF.runtime.servererrorhandling.Plugin
        if (!$result->isSuccessful()) {
            throw new \Exception($result->getErrorMessage());
        }

        $response = array(
            'success' => $result->isSuccessful(),
        );

        return $response;
    }

    /**
     * @Remote
     *
     * @param array $params
     */
    public function isCompileNeededAction(array $params)
    {
        $this->checkAccess();

        /* @var CompileNeeded $compileNeeded */
        $compileNeeded = $this->get('modera_backend_translations_tool.cache.compile_needed');
        $isCompileNeeded = $compileNeeded->get();

        return array(
            'success' => true,
            'status' => $isCompileNeeded,
        );
    }
}
