<?php

namespace Modera\BackendToolsActivityLogBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface;
use Modera\ActivityLoggerBundle\Model\ActivityInterface;
use Modera\BackendToolsActivityLogBundle\AuthorResolving\ActivityAuthorResolver;
use Modera\BackendToolsActivityLogBundle\AutoSuggest\FilterAutoSuggestService;
use Modera\BackendToolsActivityLogBundle\ModeraBackendToolsActivityLogBundle;
use Modera\ServerCrudBundle\Hydration\DoctrineEntityHydrator;
use Modera\ServerCrudBundle\Hydration\HydrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class DefaultController extends AbstractController
{
    public function __construct(
        private readonly ActivityAuthorResolver $activityAuthorResolver,
        private readonly ActivityManagerInterface $activityManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly EntityManagerInterface $entityManager,
        private readonly FilterAutoSuggestService $filterAutoSuggestService,
        private readonly HydrationService $hydrationService,
    ) {
    }

    private function checkAccess(): void
    {
        $role = ModeraBackendToolsActivityLogBundle::ROLE_ACCESS_BACKEND_TOOLS_ACTIVITY_LOG_SECTION;
        if (false === $this->authorizationChecker->isGranted($role)) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getConfig(): array
    {
        return [
            'groups' => [
                'list' => function (ActivityInterface $activity) {
                    return \array_merge(DoctrineEntityHydrator::create($this->entityManager)->hydrate(
                        entity: $activity,
                        excludeFields: ['meta', 'createdAt', 'author'],
                    ), [
                        'createdAt' => $activity->getCreatedAt()->format(\DateTime::W3C),
                        'author' => \json_encode($this->activityAuthorResolver->resolve($activity)),
                        'meta' => $activity->getMeta(),
                    ]);
                },
                'details' => function (ActivityInterface $activity) {
                    return \array_merge(DoctrineEntityHydrator::create($this->entityManager)->hydrate(
                        entity: $activity,
                        excludeFields: ['meta', 'createdAt', 'author'],
                    ), [
                        'createdAt' => $activity->getCreatedAt()->format(\DateTime::W3C),
                        'author' => $this->activityAuthorResolver->resolve($activity),
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

        $result = $this->activityManager->query($params);

        if (1 === \count($result['items'])) {
            return [
                'result' => $this->hydrationService->hydrate($result['items'][0], $this->getConfig(), 'details'),
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

        $result = $this->activityManager->query($params);

        $response = [
            'items' => [],
        ];

        /** @var ActivityInterface $activity */
        foreach ($result['items'] as $activity) {
            $response['items'][] = $this->hydrationService->hydrate($activity, $this->getConfig(), 'list');
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

        $queryType = \is_string($params['queryType']) ? $params['queryType'] : '-';
        $query = \is_string($params['query']) ? $params['query'] : '-';

        return $this->filterAutoSuggestService->suggest($queryType, $query);
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
