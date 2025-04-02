<?php

namespace Modera\BackendToolsActivityLogBundle\Controller;

use Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface;
use Modera\ActivityLoggerBundle\Model\ActivityInterface;
use Modera\BackendToolsActivityLogBundle\AuthorResolving\ActivityAuthorResolver;
use Modera\BackendToolsActivityLogBundle\AutoSuggest\FilterAutoSuggestService;
use Modera\BackendToolsActivityLogBundle\ModeraBackendToolsActivityLogBundle;
use Modera\DirectBundle\Annotation\Remote;
use Modera\ServerCrudBundle\Hydration\DoctrineEntityHydrator;
use Modera\ServerCrudBundle\Hydration\HydrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class DefaultController extends Controller
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationChecker = $authorizationChecker;
    }

    private function checkAccess(): void
    {
        $role = ModeraBackendToolsActivityLogBundle::ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION;
        if (false === $this->authorizationChecker->isGranted($role)) {
            throw $this->createAccessDeniedException();
        }
    }

    private function getHydrationService(): HydrationService
    {
        /** @var HydrationService $hydrationService */
        $hydrationService = $this->container->get('modera_server_crud.hydration.hydration_service');

        return $hydrationService;
    }

    private function getActivityManager(): ActivityManagerInterface
    {
        /** @var ActivityManagerInterface $activityManager */
        $activityManager = $this->container->get('modera_activity_logger.manager.activity_manager');

        return $activityManager;
    }

    private function getActivityAuthorResolver(): ActivityAuthorResolver
    {
        /** @var ActivityAuthorResolver $activityAuthorResolver */
        $activityAuthorResolver = $this->container->get('modera_backend_tools_activity_log.activity_author_resolver');

        return $activityAuthorResolver;
    }

    /**
     * @return array<string, mixed>
     */
    private function getConfig(): array
    {
        $authorResolver = $this->getActivityAuthorResolver();

        return [
            'groups' => [
                'list' => function (ActivityInterface $activity, $container) use ($authorResolver) {
                    $hydrator = DoctrineEntityHydrator::create(['meta', 'createdAt', 'author']);

                    return \array_merge($hydrator($activity, $container), [
                        'createdAt' => $activity->getCreatedAt()->format(\DateTime::W3C),
                        'author' => \json_encode($authorResolver->resolve($activity)),
                        'meta' => $activity->getMeta(),
                    ]);
                },
                'details' => function (ActivityInterface $activity, ContainerInterface $container) use ($authorResolver) {
                    $hydrator = DoctrineEntityHydrator::create(['meta', 'createdAt', 'author']);

                    return \array_merge($hydrator($activity, $container), [
                        'createdAt' => $activity->getCreatedAt()->format(\DateTime::W3C),
                        'author' => $authorResolver->resolve($activity),
                        'meta' => $activity->getMeta(),
                    ]);
                },
            ],
            'profiles' => [
                'list', 'details',
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
    public function getAction(array $params): array
    {
        $this->checkAccess();

        $result = $this->getActivityManager()->query($params);

        if (1 === \count($result['items'])) {
            return [
                'result' => $this->getHydrationService()->hydrate($result['items'][0], $this->getConfig(), 'details'),
                'success' => true,
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Unable to find activity by given query',
            ];
        }
    }

    /**
     * @Remote
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function listAction(array $params): array
    {
        $this->checkAccess();

        $result = $this->getActivityManager()->query($params);

        $response = [
            'items' => [],
        ];

        /** @var ActivityInterface $activity */
        foreach ($result['items'] as $activity) {
            $response['items'][] = $this->getHydrationService()->hydrate($activity, $this->getConfig(), 'list');
        }

        return \array_merge($result, $response, [
            'success' => true,
        ]);
    }

    /**
     * @Remote
     *
     * @param array<string, mixed> $params
     *
     * @return array<mixed>
     */
    public function suggestAction(array $params): array
    {
        $this->checkAccess();

        $this->validateRequiredParams($params, ['queryType', 'query']);

        /** @var FilterAutoSuggestService $service */
        $service = $this->container->get('modera_backend_tools_activity_log.auto_suggest.filter_auto_suggest_service');

        $queryType = \is_string($params['queryType']) ? $params['queryType'] : '-';
        $query = \is_string($params['query']) ? $params['query'] : '-';

        return $service->suggest($queryType, $query);
    }

    /**
     * @param array<string, mixed> $params
     * @param string[]             $requiredKeys
     *
     * @throws \RuntimeException If some of $requiredParams were not provided
     */
    private function validateRequiredParams(array $params, array $requiredKeys): void
    {
        $missingKeys = [];
        foreach ($requiredKeys as $key) {
            if (!isset($params[$key])) {
                $missingKeys[] = $key;
            }
        }

        if (\count($missingKeys) > 0) {
            throw new \RuntimeException('These request parameters must be provided: '.\implode(', ', $missingKeys));
        }
    }
}
