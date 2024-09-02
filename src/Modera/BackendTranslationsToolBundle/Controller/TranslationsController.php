<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Modera\BackendTranslationsToolBundle\Cache\CompileNeeded;
use Modera\BackendTranslationsToolBundle\Contributions\FiltersProvider;
use Modera\BackendTranslationsToolBundle\DependencyInjection\ModeraBackendTranslationsToolExtension;
use Modera\BackendTranslationsToolBundle\Filtering\FilterInterface;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\DirectBundle\Annotation\Remote;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\ExceptionHandling\ExceptionHandlerInterface;
use Modera\ServerCrudBundle\Exceptions\BadRequestException;
use Modera\TranslationsBundle\Compiler\TranslationsCompiler;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TranslationsController extends AbstractCrudController
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function getContainer(): ContainerInterface
    {
        /** @var ContainerInterface $container */
        $container = $this->container;

        return $container;
    }

    private function checkAccess(): void
    {
        $role = ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION;
        if (false === $this->authorizationChecker->isGranted($role)) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function hydrateLanguageTranslationToken(LanguageTranslationToken $ltt): array
    {
        return [
            'id' => $ltt->getId(),
            'isNew' => $ltt->isNew(),
            'translation' => $ltt->getTranslation(),
            'locale' => $ltt->getLanguage() ? $ltt->getLanguage()->getLocale() : null,
            'language' => $ltt->getLanguage() ? $ltt->getLanguage()->getName() : null,
        ];
    }

    public function getConfig(): array
    {
        return [
            'entity' => TranslationToken::class,
            'security' => [
                'role' => ModeraBackendTranslationsToolBundle::ROLE_ACCESS_BACKEND_TOOLS_TRANSLATIONS_SECTION,
            ],
            'hydration' => [
                'groups' => [
                    'list' => function (TranslationToken $translationToken) {
                        $translations = [];
                        foreach ($translationToken->getLanguageTranslationTokens() as $ltt) {
                            if ($ltt->getLanguage() && $ltt->getLanguage()->isEnabled()) {
                                $translations[$ltt->getLanguage()->getId()] = $this->hydrateLanguageTranslationToken($ltt);
                            }
                        }

                        return [
                            'id' => $translationToken->getId(),
                            'domain' => $translationToken->getDomain(),
                            'tokenName' => $translationToken->getTokenName(),
                            'isObsolete' => $translationToken->isObsolete(),
                            'translations' => $translations,
                        ];
                    },
                ],
                'profiles' => [
                    'list',
                ],
            ],
        ];
    }

    /**
     * @Remote
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function listWithFiltersAction(array $params): array
    {
        $this->checkAccess();

        try {
            $filterId = null;
            $filterValue = null;
            if (isset($params['filter']) && \is_array($params['filter'])) {
                foreach ($params['filter'] as $filter) {
                    if (isset($filter['property']) && '__filter__' == $filter['property']) {
                        $parts = \explode('-', $filter['value'], 2);
                        $filterId = $parts[0];
                        if (isset($parts[1])) {
                            $filterValue = $parts[1];
                        }
                        break;
                    }
                }

                if ($filterValue) {
                    $params['filter'] = [
                        ['property' => 'languageTranslationTokens.language.isEnabled', 'value' => 'eq:true'],
                        [
                            ['property' => 'domain', 'value' => 'eq:'.$filterValue],
                            ['property' => 'tokenName', 'value' => 'like:%'.$filterValue.'%'],
                            ['property' => 'languageTranslationTokens.translation', 'value' => 'like:%'.$filterValue.'%'],
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

            /** @var FiltersProvider $filtersProvider */
            $filtersProvider = $this->getContainer()->get('modera_backend_translations_tool.filters_provider');

            $filter = null;
            $filters = $filtersProvider->getItems();
            if (!isset($filters['translation_token']) || !\is_array($filters['translation_token'])) {
                $filters['translation_token'] = [];
            }
            foreach ($filters['translation_token'] as $iteratedFilter) {
                /** @var FilterInterface $iteratedFilter */
                if ($iteratedFilter->getId() == $filterId && $iteratedFilter->isAllowed()) {
                    $filter = $iteratedFilter;
                    break;
                }
            }

            if (!$filter) {
                throw new \RuntimeException(\sprintf('Filter with given parameter "%s" not found', $filterId));
            }

            $result = $filter->getResult($params);
            if (!isset($result['items']) || !\is_array($result['items'])) {
                $result['items'] = [];
            }

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
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    public function importAction(array $params): array
    {
        $this->checkAccess();

        /** @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $app = new Application($kernel);
        $app->setAutoExit(false);

        /** @var string $cmd */
        $cmd = $this->getContainer()->getParameter(ModeraBackendTranslationsToolExtension::CONFIG_KEY.'.import_cmd');
        $input = new StringInput($cmd);
        $input->setInteractive(false);

        $result = $app->run($input, new NullOutput());

        return [
            'success' => (0 === $result),
            'updated_models' => [
                'modera.translations_bundle.translation_token' => [],
            ],
        ];
    }

    /**
     * @Remote
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function compileAction(array $params): array
    {
        $this->checkAccess();

        /** @var TranslationsCompiler $compiler */
        $compiler = $this->getContainer()->get('modera_translations.compiler.translations_compiler');

        /** @var bool $onlyTranslated */
        $onlyTranslated = $this->getContainer()->getParameter(ModeraBackendTranslationsToolExtension::CONFIG_KEY.'.compile_only_translated');

        $result = $compiler->compile($onlyTranslated);

        if ($result->isSuccessful()) {
            /** @var CompileNeeded $compileNeeded */
            $compileNeeded = $this->getContainer()->get('modera_backend_translations_tool.cache.compile_needed');
            $compileNeeded->set(false);
        } else {
            /** @var KernelInterface $kernel */
            $kernel = $this->getContainer()->get('kernel');

            // if activity logger bundle is available then logging the error there as well
            $bundles = $kernel->getBundles();
            if (isset($bundles['ModeraActivityLoggerBundle'])) {
                /** @var UserInterface $user */
                $user = $this->getUser();

                /** @var LoggerInterface $logger */
                $logger = $this->getContainer()->get('modera_activity_logger.manager.activity_manager');

                $logger->error(
                    // 'message' field for Activity entity is mapped as "string", so we can't put there a whole message
                    "Failed to compile translations, details: \n\n".\substr($result->getErrorMessage(), 0, 150).'...',
                    [
                        'type' => 'translations',
                        'author' => $user->getUsername(),
                    ]
                );
            }
        }

        // will be handled by MF.runtime.servererrorhandling.Plugin
        if (!$result->isSuccessful()) {
            throw new \Exception($result->getErrorMessage());
        }

        $response = [
            'success' => $result->isSuccessful(),
        ];

        return $response;
    }

    /**
     * @Remote
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function isCompileNeededAction(array $params): array
    {
        $this->checkAccess();

        /** @var CompileNeeded $compileNeeded */
        $compileNeeded = $this->getContainer()->get('modera_backend_translations_tool.cache.compile_needed');
        $isCompileNeeded = $compileNeeded->get();

        return [
            'success' => true,
            'status' => $isCompileNeeded,
        ];
    }
}
