<?php

namespace Modera\FileRepositoryBundle\Tests\Unit\Authoring;

use Modera\FileRepositoryBundle\Authoring\AuthoringInterceptor;
use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class DummyUser implements UserInterface
{
    public ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }
}

class AuthoringInterceptorTest extends \PHPUnit\Framework\TestCase
{
    public function testOnPutHappyPath(): void
    {
        $dummyUser = new DummyUser();
        $dummyUser->id = 777;

        $tokenMock = \Phake::mock(UsernamePasswordToken::class);
        \Phake::when($tokenMock)
            ->getUser()
            ->thenReturn($dummyUser)
        ;

        $tokenStorageMock = \Phake::mock(TokenStorageInterface::class);
        \Phake::when($tokenStorageMock)
            ->getToken()
            ->thenReturn($tokenMock)
        ;

        $ai = new AuthoringInterceptor($tokenStorageMock);

        $storedFile = \Phake::mock(StoredFile::class);

        $ai->onPut($storedFile, new \SplFileInfo(__FILE__), \Phake::mock(Repository::class));

        \Phake::verify($storedFile)
            ->setAuthor(777)
        ;
    }

    public function testOnPutUserNotAuthenticated(): void
    {
        $tokenMock = \Phake::mock(UsernamePasswordToken::class);

        $tokenStorageMock = \Phake::mock(TokenStorageInterface::class);
        \Phake::when($tokenStorageMock)
            ->getToken()
            ->thenReturn($tokenMock)
        ;

        $ai = new AuthoringInterceptor($tokenStorageMock);

        $storedFile = \Phake::mock(StoredFile::class);

        $ai->onPut($storedFile, new \SplFileInfo(__FILE__), \Phake::mock(Repository::class));

        \Phake::verifyNoInteraction($storedFile);
    }

    public function testOnPutUserObjectHasNoId(): void
    {
        $dummyUser = null;

        $tokenMock = \Phake::mock(UsernamePasswordToken::class);
        \Phake::when($tokenMock)
            ->getUser()
            ->thenReturn($dummyUser)
        ;

        $tokenStorageMock = \Phake::mock(TokenStorageInterface::class);
        \Phake::when($tokenStorageMock)
            ->getToken()
            ->thenReturn($tokenMock)
        ;

        $ai = new AuthoringInterceptor($tokenStorageMock);

        $storedFile = \Phake::mock(StoredFile::class);

        $ai->onPut($storedFile, new \SplFileInfo(__FILE__), \Phake::mock(Repository::class));

        \Phake::verifyNoInteraction($storedFile);
    }

    public function testOnPutAuthorIsAlreadySpecified(): void
    {
        $dummyUser = new DummyUser();
        $dummyUser->id = 777;

        $tokenMock = \Phake::mock(UsernamePasswordToken::class);
        \Phake::when($tokenMock)
            ->getUser()
            ->thenReturn($dummyUser)
        ;

        $tokenStorageMock = \Phake::mock(TokenStorageInterface::class);
        \Phake::when($tokenStorageMock)
            ->getToken()
            ->thenReturn($tokenMock)
        ;

        $ai = new AuthoringInterceptor($tokenStorageMock);

        $storedFile = \Phake::mock(StoredFile::class);
        \Phake::verifyNoFurtherInteraction($storedFile);

        $ai->onPut(
            $storedFile,
            new \SplFileInfo(__FILE__),
            \Phake::mock(Repository::class),
            ['author' => 'bob'],
        );
    }
}
