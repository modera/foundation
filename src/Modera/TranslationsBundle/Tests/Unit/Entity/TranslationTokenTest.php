<?php

namespace Modera\TranslationsBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Modera\TranslationsBundle\Entity\TranslationToken;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class TranslationTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersChaining()
    {
        $token = new TranslationToken();

        $translationTokensMock = \Phake::mock(ArrayCollection::class);

        $this->assertSame($token, $token->setBundleName('foo-bn'));
        $this->assertSame($token, $token->setTokenName('foo-tokenname'));
        $this->assertSame($token, $token->setDomain('foo-domain'));
        $this->assertSame($token, $token->setLanguageTranslationTokens($translationTokensMock));
        $this->assertSame($token, $token->setObsolete(true));
        $this->assertSame($token, $token->setSource('foo-source'));

        $this->assertEquals('foo-bn', $token->getBundleName());
        $this->assertEquals('foo-tokenname', $token->getTokenName());
        $this->assertEquals('foo-domain', $token->getDomain());
        $this->assertSame($translationTokensMock, $token->getLanguageTranslationTokens());
        $this->assertTrue($token->isObsolete());
        $this->assertEquals('foo-source', $token->getSource());
    }
}