<?php

namespace Modera\TranslationsBundle\Tests\Functional\Command;

use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Tests\Functional\AbstractFunctionalTestCase;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ImportTranslationsCommandTest extends AbstractFunctionalTestCase
{
    // override
    public static function doSetUpBeforeClass()
    {
        self::setUpDatabase();
    }

    // override
    public static function doTearDownAfterClass()
    {
        self::dropDatabase();
    }

    private function assertToken($token, $tokenName = 'Test token')
    {
        /* @var TranslationToken $token */
        $this->assertInstanceOf(TranslationToken::clazz(), $token);
        $this->assertFalse($token->isObsolete());
        $this->assertEquals('ModeraTranslationsDummyBundle', $token->getBundleName());
        $this->assertEquals('messages', $token->getDomain());
        $this->assertEquals($tokenName, $token->getTokenName());
        $this->assertEquals(1, count($token->getTranslations()));
        $this->assertEquals(1, count($token->getLanguageTranslationTokens()));

        $translations = $token->getTranslations();
        /* @var LanguageTranslationToken $ltt */
        foreach ($token->getLanguageTranslationTokens() as $ltt) {
            $this->assertTrue($ltt->isNew());
            $this->assertEquals('en', $ltt->getLanguage()->getLocale());
            $this->assertEquals($tokenName, $ltt->getTranslation());
            $this->assertEquals(array(
                'id' => $ltt->getId(),
                'isNew' => $ltt->isNew(),
                'translation' => $ltt->getTranslation(),
                'locale' => $ltt->getLanguage()->getLocale(),
                'language' => $ltt->getLanguage()->getName(),
            ), $translations[$ltt->getLanguage()->getId()]);
        }
    }

    public function testImport()
    {
        $tokens = self::$em->getRepository(TranslationToken::clazz())->findAll();
        $this->assertEquals(0, count($tokens));

        $this->launchImportCommand();

        $tokens = self::$em->getRepository(TranslationToken::clazz())->findAll();
        $this->assertEquals(2, count($tokens));

        $tokens = self::$em->getRepository(TranslationToken::clazz())->findBy(array(
            'bundleName' => 'ModeraTranslationsDummyBundle',
            'tokenName' => 'Test token'
        ));
        $this->assertCount(1, $tokens);
        $this->assertToken($tokens[0]);

        $token = self::$em->getRepository(TranslationToken::clazz())->findOneBy(array(
            'bundleName' => 'ModeraTranslationsDummyBundle',
            'tokenName' => 'Test token only in twig'
        ));
        $this->assertToken($token, 'Test token only in twig');

        $token = self::$em->getRepository(TranslationToken::clazz())->findOneBy(array(
            'bundleName' => 'undefined',
        ));
        $this->assertFalse($token instanceof TranslationToken);
    }
}
