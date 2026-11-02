<?php

namespace Modera\SecurityBundle\Tests\Functional\EventListener;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\EventListener\AuthenticationSubscriber;
use Modera\SecurityBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationSubscriberTest extends FunctionalTestCase
{
    private static SchemaTool $st;

    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema(self::getMetadata());
    }

    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema(self::getMetadata());
    }

    public function testLoginStoredWithoutLeavingUserChanged(): void
    {
        $user = $this->createUser('stored', UserInterface::STATE_NEW, null);

        $this->authenticate($user);

        $uow = self::$em->getUnitOfWork();
        $uow->computeChangeSets();
        $this->assertSame([], $uow->getEntityChangeSet($user));
        $this->assertSame(UserInterface::STATE_ACTIVE, $user->getState());
        $this->assertNotNull($this->storedLastLogin($user));
    }

    private function createUser(string $username, int $state, ?\DateTimeInterface $lastLogin): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setPassword('pwd');
        $user->setEmail($username.'@email.test');
        $user->setState($state);
        if ($lastLogin) {
            $user->setLastLogin($lastLogin);
        }
        self::$em->persist($user);
        self::$em->flush();

        return $user;
    }

    private function authenticate(User $user): void
    {
        $subscriber = new AuthenticationSubscriber(self::getContainer()->get('doctrine'));
        $subscriber->onAuthenticationSuccess(
            new AuthenticationEvent(new UsernamePasswordToken($user, 'main', $user->getRoles())),
        );
    }

    private function storedLastLogin(User $user): ?string
    {
        $metadata = self::$em->getClassMetadata(User::class);

        $value = self::$em->getConnection()->fetchOne(
            \sprintf('SELECT %s FROM %s WHERE id = ?', $metadata->getColumnName('lastLogin'), $metadata->getTableName()),
            [$user->getId()],
        );

        return false === $value ? null : $value;
    }

    /**
     * @return array<int, \Doctrine\ORM\Mapping\ClassMetadata<object>>
     */
    private static function getMetadata(): array
    {
        return [
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionCategory::class),
        ];
    }
}
