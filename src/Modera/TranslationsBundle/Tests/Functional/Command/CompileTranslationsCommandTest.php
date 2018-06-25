<?php

namespace Modera\TranslationsBundle\Tests\Functional\Command;

use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CompileTranslationsCommandTest extends ImportTranslationsCommandTest
{
    public function testCompile()
    {
        $fs = new Filesystem();
        $resourcesDir = 'app/Resources';
        $basePath = dirname(self::$kernel->getContainer()->get('kernel')->getRootdir());

        $transDir = $resourcesDir.'/translations';
        $transPath = $basePath.'/'.$transDir;

        $this->launchImportCommand();
        $this->launchCompileCommand();

        $this->assertTrue($fs->exists($transPath));
        $this->assertTrue($fs->exists($transPath.'/messages.en.yml'));

        $catalogue = new MessageCatalogue('en');
        $loader = self::$kernel->getContainer()->get('translation.loader');
        $loader->loadMessages(dirname($transPath), $catalogue);
        $messages = $catalogue->all('messages');

        $this->assertEquals(3, count($messages));
        $this->assertTrue(isset($messages['Test token']));
        $this->assertEquals('Test token', $messages['Test token']);

        $this->assertTrue(isset($messages['Test token only in twig']));
        $this->assertEquals('Test token only in twig', $messages['Test token only in twig']);

        $this->assertTrue(isset($messages['This token is only in SecondDummy bundle']));
        $this->assertEquals('This token is only in SecondDummy bundle', $messages['This token is only in SecondDummy bundle']);

        if ($fs->exists($transPath)) {
            $fs->remove($transPath);
        }
    }

    public function testCompileTranslated()
    {
        $fs = new Filesystem();
        $resourcesDir = 'app/Resources';
        $basePath = dirname(self::$kernel->getContainer()->get('kernel')->getRootdir());

        $transDir = $resourcesDir.'/translations';
        $transPath = $basePath.'/'.$transDir;

        $language = new Language();
        $language->setLocale('et');
        $language->setEnabled(true);
        self::$em->persist($language);
        self::$em->flush();

        $this->launchImportCommand();

        /**@var $tt TranslationToken */
        $tt = self::$em->getRepository(TranslationToken::clazz())->findOneBy([
            'tokenName' => 'Test token'
        ]);
        foreach ($tt->getLanguageTranslationTokens() as $languageTranslationToken) {
            /**@var $languageTranslationToken \Modera\TranslationsBundle\Entity\LanguageTranslationToken */
            if ($languageTranslationToken->getLanguage()->getLocale() == 'et') {
                $languageTranslationToken->setTranslation('Test token translated EE');
                self::$em->persist($languageTranslationToken);
                self::$em->flush();
            }
        }

        $this->launchCompileCommand();

        $this->assertTrue($fs->exists($transPath));
        $this->assertTrue($fs->exists($transPath.'/messages.et.yml'));

        $catalogue = new MessageCatalogue('et');
        $loader = self::$kernel->getContainer()->get('translation.loader');
        $loader->loadMessages(dirname($transPath), $catalogue);
        $messages = $catalogue->all('messages');

        $this->assertEquals(3, count($messages));
        $this->assertTrue(isset($messages['Test token']));
        $this->assertEquals('Test token translated EE', $messages['Test token']);

        $this->assertTrue(isset($messages['Test token only in twig']));
        $this->assertEquals('Test token only in twig', $messages['Test token only in twig']);

        $this->assertTrue(isset($messages['This token is only in SecondDummy bundle']));
        $this->assertEquals('This token is only in SecondDummy bundle', $messages['This token is only in SecondDummy bundle']);

        if ($fs->exists($transPath)) {
            $fs->remove($transPath);
        }
    }
}
