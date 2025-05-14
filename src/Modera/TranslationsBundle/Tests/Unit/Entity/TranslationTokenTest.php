<?php

namespace Modera\TranslationsBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;

class TranslationTokenTest extends \PHPUnit\Framework\TestCase
{
    public function testSettersChaining(): void
    {
        $token = new TranslationToken();

        $translationTokensMock = \Phake::mock(ArrayCollection::class);

        $this->assertSame($token, $token->setTokenName('foo-tokenname'));
        $this->assertSame($token, $token->setDomain('foo-domain'));
        $this->assertSame($token, $token->setLanguageTranslationTokens($translationTokensMock));
        $this->assertSame($token, $token->setObsolete(true));

        $this->assertEquals('foo-tokenname', $token->getTokenName());
        $this->assertEquals('foo-domain', $token->getDomain());
        $this->assertSame($translationTokensMock, $token->getLanguageTranslationTokens());
        $this->assertTrue($token->isObsolete());
    }

    public function testCreateLanguageToken(): void
    {
        $lang = \Phake::mock(Language::class);

        $token = new TranslationToken();

        $languageToken = $token->createLanguageToken($lang);

        $this->assertInstanceOf(LanguageTranslationToken::class, $languageToken);
        $this->assertSame($token, $languageToken->getTranslationToken());
        $this->assertSame($lang, $languageToken->getLanguage());

        $this->assertSame([$languageToken], $token->getLanguageTranslationTokens()->toArray());
    }
}
