<?php

namespace Modera\TranslationsBundle\Tests\Functional\Command;

use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;

class CompileTranslationsCommandTest extends ImportTranslationsCommandTest
{
    public function testCompile(): void
    {
        $fs = new Filesystem();
        $resourcesDir = 'app/Resources';
        $basePath = self::$kernel->getContainer()->get('kernel')->getProjectDir();

        $transDir = $resourcesDir.'/translations';
        $transPath = $basePath.'/'.$transDir;

        $this->launchImportCommand();
        $this->launchCompileCommand();

        $this->assertTrue($fs->exists($transPath));
        $this->assertTrue($fs->exists($transPath.'/messages.en.yaml'));

        $catalogue = new MessageCatalogue('en');
        $loader = self::$kernel->getContainer()->get('modera_translations.tests.translation_reader');
        $loader->read(\dirname($transPath), $catalogue);
        $messages = $catalogue->all('messages');

        $this->assertEquals(3, \count($messages));
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
            // $fs->remove($transPath);
        }
    }

    public function testCompileTranslated(): void
    {
        $fs = new Filesystem();
        $resourcesDir = 'app/Resources';
        $basePath = self::$kernel->getContainer()->get('kernel')->getProjectDir();

        $transDir = $resourcesDir.'/translations';
        $transPath = $basePath.'/'.$transDir;

        $language = new Language();
        $language->setLocale('et');
        $language->setEnabled(true);
        self::$em->persist($language);
        self::$em->flush();

        $this->launchImportCommand();

        /** @var TranslationToken $tt */
        $tt = self::$em->getRepository(TranslationToken::class)->findOneBy([
            'tokenName' => 'Test token',
        ]);
        foreach ($tt->getLanguageTranslationTokens() as $languageTranslationToken) {
            /** @var LanguageTranslationToken $languageTranslationToken */
            if ('et' === $languageTranslationToken->getLanguage()->getLocale()) {
                $languageTranslationToken->setTranslation('Test token translated EE');
                self::$em->persist($languageTranslationToken);
                self::$em->flush();
            }
        }

        $this->launchCompileCommand();

        $this->assertTrue($fs->exists($transPath));
        $this->assertTrue($fs->exists($transPath.'/messages.et.yaml'));

        $catalogue = new MessageCatalogue('et');
        $loader = self::$kernel->getContainer()->get('modera_translations.tests.translation_reader');
        $loader->read(\dirname($transPath), $catalogue);
        $messages = $catalogue->all('messages');

        $this->assertEquals(3, \count($messages));
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
            // $fs->remove($transPath);
        }
    }
}
