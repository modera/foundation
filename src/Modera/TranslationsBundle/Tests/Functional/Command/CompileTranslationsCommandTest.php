<?php

namespace Modera\TranslationsBundle\Tests\Functional\Command;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageCatalogue;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Modera\LanguagesBundle\Entity\Language;

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

        $this->launchImportCommand(array('--mark-as-translated' => true));
        $this->launchCompileCommand();

        $this->assertTrue($fs->exists($transPath));
        $this->assertTrue($fs->exists($transPath.'/messages.en.yml'));

        $catalogue = new MessageCatalogue('en');
        $loader = self::$kernel->getContainer()->get('modera_translations.translation.reader');
        $loader->read(dirname($transPath), $catalogue);
        $messages = $catalogue->all('messages');

        $this->assertEquals(3, count($messages));
        $this->assertTrue(isset($messages['Test token']));
        $this->assertEquals('Test token', $messages['Test token']);

        $this->assertTrue(isset($messages['Test token only in twig']));
        $this->assertEquals('Test token only in twig', $messages['Test token only in twig']);

        $this->assertTrue(isset($messages['This token is only in SecondDummy bundle']));
        $this->assertEquals('This token is only in SecondDummy bundle', $messages['This token is only in SecondDummy bundle']);

        if ($fs->exists($transPath)) {
            foreach (Finder::create()->files()->in($transPath) as $file) {
                $fs->remove($file->getRealPath());
            }
            //$fs->remove($transPath);
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

        $this->launchImportCommand(array('--mark-as-translated' => true));

        /* @var TranslationToken $tt */
        $tt = self::$em->getRepository(TranslationToken::clazz())->findOneBy([
            'tokenName' => 'Test token'
        ]);
        foreach ($tt->getLanguageTranslationTokens() as $languageTranslationToken) {
            /* @var LanguageTranslationToken $languageTranslationToken */
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
        $loader = self::$kernel->getContainer()->get('modera_translations.translation.reader');
        $loader->read(dirname($transPath), $catalogue);
        $messages = $catalogue->all('messages');

        $this->assertEquals(3, count($messages));
        $this->assertTrue(isset($messages['Test token']));
        $this->assertEquals('Test token translated EE', $messages['Test token']);

        $this->assertTrue(isset($messages['Test token only in twig']));
        $this->assertEquals('Test token only in twig', $messages['Test token only in twig']);

        $this->assertTrue(isset($messages['This token is only in SecondDummy bundle']));
        $this->assertEquals('This token is only in SecondDummy bundle', $messages['This token is only in SecondDummy bundle']);

        if ($fs->exists($transPath)) {
            foreach (Finder::create()->files()->in($transPath) as $file) {
                $fs->remove($file->getRealPath());
            }
            //$fs->remove($transPath);
        }
    }
}
