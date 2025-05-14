<?php

namespace Modera\BackendTranslationsToolBundle\Controller;

use Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface;
use Modera\BackendTranslationsToolBundle\Cache\CompileNeeded;
use Modera\BackendTranslationsToolBundle\Contributions\FiltersProvider;
use Modera\BackendTranslationsToolBundle\DependencyInjection\ModeraBackendTranslationsToolExtension;
use Modera\BackendTranslationsToolBundle\Filtering\FilterInterface;
use Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\ExceptionHandling\ExceptionHandlerInterface;
use Modera\ServerCrudBundle\Exceptions\BadRequestException;
use Modera\TranslationsBundle\Compiler\TranslationsCompiler;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class TranslationsController extends AbstractCrudController
{
    public function __construct(
        private readonly ActivityManagerInterface $activityManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly CompileNeeded $compileNeeded,
        private readonly ExtensionProvider $extensionProvider,
        private readonly KernelInterface $kernel,
        private readonly TranslationsCompiler $translationsCompiler,
    ) {
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
            'locale' => $ltt->getLanguage()?->getLocale(),
            'language' => $ltt->getLanguage()?->getName(),
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
            if (\is_array($params['filter'] ?? null)) {
                /** @var array{
                 *      'property'?: string,
                 *     'value': string,
                 * } $filter
                 */
                foreach ($params['filter'] as $filter) {
                    if (\is_string($filter['property'] ?? null) && '__filter__' == $filter['property']) {
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

            $filter = null;

            $providerId = 'modera_backend_translations_tool.filters';
            if ($this->extensionProvider->has($providerId)) {
                /** @var FiltersProvider $filtersProvider */
                $filtersProvider = $this->extensionProvider->get($providerId);

                $filters = $filtersProvider->getItems();
                if (!\is_array($filters['translation_token'] ?? null)) {
                    $filters['translation_token'] = [];
                }
                foreach ($filters['translation_token'] as $iteratedFilter) {
                    /** @var FilterInterface $iteratedFilter */
                    if ($iteratedFilter->getId() == $filterId && $iteratedFilter->isAllowed()) {
                        $filter = $iteratedFilter;
                        break;
                    }
                }
            }

            if (!$filter) {
                throw new \RuntimeException(\sprintf('Filter with given parameter "%s" not found', $filterId));
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
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    public function importAction(array $params): array
    {
        $this->checkAccess();

        $app = new Application($this->kernel);
        $app->setAutoExit(false);

        /** @var string $cmd */
        $cmd = $this->getParameter(ModeraBackendTranslationsToolExtension::CONFIG_KEY.'.import_cmd');
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

        /** @var bool $onlyTranslated */
        $onlyTranslated = $this->getParameter(ModeraBackendTranslationsToolExtension::CONFIG_KEY.'.compile_only_translated');

        $result = $this->translationsCompiler->compile($onlyTranslated);

        if ($result->isSuccessful()) {
            $this->compileNeeded->set(false);
        } else {
            // if activity logger bundle is available then logging the error there as well
            $bundles = $this->kernel->getBundles();
            if (isset($bundles['ModeraActivityLoggerBundle'])) {
                /** @var UserInterface $user */
                $user = $this->getUser();

                $this->activityManager->error(
                    // 'message' field for Activity entity is mapped as "string", so we can't put there a whole message
                    "Failed to compile translations, details: \n\n".\substr($result->getErrorMessage(), 0, 150).'...',
                    [
                        'type' => 'translations',
                        'author' => $user->getUserIdentifier(),
                    ]
                );
            }
        }

        // will be handled by MF.runtime.servererrorhandling.Plugin
        if (!$result->isSuccessful()) {
            throw new \RuntimeException($result->getErrorMessage());
        }

        return [
            'success' => $result->isSuccessful(),
        ];
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

        $isCompileNeeded = $this->compileNeeded->get();

        return [
            'success' => true,
            'status' => $isCompileNeeded,
        ];
    }
}
