<?php

namespace Modera\BackendToolsActivityLogBundle\AutoSuggest;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ActivityLoggerBundle\Manager\ActivityManagerInterface;
use Modera\ActivityLoggerBundle\Model\ActivityInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class FilterAutoSuggestService
{
    private EntityManagerInterface $em;

    private ActivityManagerInterface $activityManager;

    public function __construct(
        EntityManagerInterface $em,
        ActivityManagerInterface $activityManager
    ) {
        $this->em = $em;
        $this->activityManager = $activityManager;
    }

    protected function stringifyUser(User $user): ?string
    {
        return $user->getFullName()
              ? \sprintf('%s (%s)', $user->getFullName(), $user->getUsername())
              : $user->getUsername();
    }

    /**
     * @return array<mixed>
     */
    public function suggest(string $queryType, string $query): array
    {
        if ('user' == $queryType) {
            $dql = $this->em->createQuery(\sprintf(
                'SELECT u FROM %s u WHERE u.firstName LIKE ?0 OR u.lastName LIKE ?0 OR u.username LIKE ?0 OR u.email LIKE ?0',
                User::class
            ));
            $dql->setParameter(0, '%'.$query.'%');

            /** @var User[] $users */
            $users = $dql->getResult();

            $rawResult = [];
            foreach ($users as $user) {
                $value = $this->stringifyUser($user);

                $rawResult[] = [
                    'id' => $user->getId(),
                    'value' => $value,
                ];
            }

            return $rawResult;
        } elseif ('exact-user' == $queryType) { // find by ID
            $user = $this->em->find(User::class, $query);

            if (!$user) {
                throw new \DomainException(T::trans('Unable to find a user "%username%"', ['%username%' => $query]));
            }

            return [
                [
                    'id' => $user->getId(),
                    'value' => $this->stringifyUser($user),
                ],
            ];
        } elseif ('eventType' == $queryType) {
            $activities = $this->activityManager->query([
                'filter' => [
                    [
                        'property' => 'type',
                        'value' => 'like:%'.$query.'%',
                    ],
                ],
            ]);

            $rawResult = [];
            /** @var ActivityInterface $activity */
            foreach ($activities['items'] as $activity) {
                $rawResult[] = $activity->getType();
            }

            $rawResult = \array_values(\array_unique($rawResult));

            $result = [];
            foreach ($rawResult as $item) {
                $result[] = [
                    'id' => $item,
                    'value' => $item,
                ];
            }

            return $result;
        }

        throw new \DomainException(T::trans('Undefined query type "%value%"', ['%value%' => $queryType]));
    }
}
