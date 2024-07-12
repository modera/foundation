<?php

namespace Modera\BackendToolsActivityLogBundle\AuthorResolving;

use Doctrine\ORM\EntityManagerInterface;
use Modera\ActivityLoggerBundle\Model\ActivityInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ActivityAuthorResolver
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return array<mixed>
     */
    public function resolve(ActivityInterface $activity): array
    {
        $isId = \preg_match('/^[0-9]+$/', $activity->getAuthor());

        if ($isId) {
            /** @var ?User $user */
            $user = $this->em->find(User::class, $activity->getAuthor());
            if ($user) {
                return [
                    'id' => $user->getId(),
                    'isUser' => true,
                    'fullName' => $user->getFullName(),
                    'username' => $user->getUsername(),
                ];
            } else {
                return [
                    'isUser' => false,
                    'identity' => $activity->getAuthor(),
                ];
            }
        } else {
            return [
                'isUser' => false,
                'identity' => $activity->getAuthor() ? $activity->getAuthor() : T::trans('Unknown'),
            ];
        }
    }
}
