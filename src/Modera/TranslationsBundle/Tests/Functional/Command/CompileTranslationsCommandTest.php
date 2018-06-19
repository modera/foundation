<?php

namespace Modera\TranslationsBundle\Tests\Functional\Command;

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

        $bundleName = 'ModeraTranslationsDummyBundle';
        $bundleTransDir = $resourcesDir.'/translations'.'/'.$bundleName;
        $bundleTransPath = $basePath.'/'.$bundleTransDir;

        $this->launchImportCommand();
        $this->launchCompileCommand();

        $this->assertTrue($fs->exists($bundleTransPath));
        $this->assertTrue($fs->exists($bundleTransPath.'/messages.en.yml'));

        $catalogue = new MessageCatalogue('en');
        $loader = self::$kernel->getContainer()->get('translation.loader');
        $loader->loadMessages(dirname($bundleTransPath), $catalogue);
        $messages = $catalogue->all('messages');

        $this->assertEquals(2, count($messages));
        $this->assertTrue(isset($messages['Test token']));
        $this->assertEquals('Test token', $messages['Test token']);

        $this->assertTrue(isset($messages['Test token only in twig']));
        $this->assertEquals('Test token only in twig', $messages['Test token only in twig']);

        if ($fs->exists($bundleTransPath)) {
            $fs->remove($bundleTransPath);
        }
    }
}
