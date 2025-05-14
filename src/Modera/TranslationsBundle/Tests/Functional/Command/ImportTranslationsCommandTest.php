<?php

namespace Modera\TranslationsBundle\Tests\Functional\Command;

use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Modera\TranslationsBundle\Tests\Functional\AbstractFunctionalTestCase;

class ImportTranslationsCommandTest extends AbstractFunctionalTestCase
{
    // override
    public static function doSetUpBeforeClass(): void
    {
        self::setUpDatabase();
    }

    // override
    public static function doTearDownAfterClass(): void
    {
        self::dropDatabase();
    }

    private function assertToken(?TranslationToken $token, $tokenName = 'Test token'): void
    {
        $this->assertInstanceOf(TranslationToken::class, $token);
        $this->assertFalse($token->isObsolete());
        $this->assertEquals('messages', $token->getDomain());
        $this->assertEquals($tokenName, $token->getTokenName());
        $this->assertEquals(1, \count($token->getLanguageTranslationTokens()));

        /** @var LanguageTranslationToken $ltt */
        foreach ($token->getLanguageTranslationTokens() as $ltt) {
            $this->assertTrue($ltt->isNew());
            $this->assertEquals('en', $ltt->getLanguage()->getLocale());
            $this->assertEquals($tokenName, $ltt->getTranslation());
        }
    }

    public function testImport(): void
    {
        $tokens = self::$em->getRepository(TranslationToken::class)->findAll();
        $this->assertEquals(0, \count($tokens));

        $this->launchImportCommand();

        $tokens = self::$em->getRepository(TranslationToken::class)->findAll();
        $this->assertEquals(3, \count($tokens));

        $tokens = self::$em->getRepository(TranslationToken::class)->findBy([
            'tokenName' => 'Test token',
        ]);
        $this->assertCount(1, $tokens);
        $this->assertToken($tokens[0]);

        $token = self::$em->getRepository(TranslationToken::class)->findOneBy([
            'tokenName' => 'Test token only in twig',
        ]);
        $this->assertToken($token, 'Test token only in twig');

        $token = self::$em->getRepository(TranslationToken::class)->findOneBy([
            'tokenName' => 'This token is only in SecondDummy bundle',
        ]);
        $this->assertToken($token, 'This token is only in SecondDummy bundle');

        $token = self::$em->getRepository(TranslationToken::class)->findOneBy([
            'tokenName' => 'undefined',
        ]);
        $this->assertFalse($token instanceof TranslationToken);
    }
}
