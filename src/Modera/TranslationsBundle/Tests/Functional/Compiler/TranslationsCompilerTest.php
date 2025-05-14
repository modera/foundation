<?php

namespace Modera\TranslationsBundle\Tests\Functional\Compiler;

use Doctrine\ORM\EntityManagerInterface;
use Modera\TranslationsBundle\Compiler\TranslationsCompiler;
use Modera\TranslationsBundle\Tests\Functional\AbstractFunctionalTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Smoke test.
 */
class TranslationsCompilerTest extends AbstractFunctionalTestCase
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

    public function testCompile(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->getConnection()->beginTransaction();

        /** @var KernelInterface $kernel */
        $kernel = self::getContainer()->get('kernel');

        /** @var TranslationsCompiler $compiler */
        $compiler = self::getContainer()->get('modera_translations.tests.translation_compiler');

        $this->launchImportCommand();

        $compiler->compile();

        $translationsDir = $kernel->getProjectDir().'/app/Resources/translations';

        $fs = new Filesystem();
        $discoveredFiles = [];
        foreach (Finder::create()->in($translationsDir) as $file) {
            $discoveredFiles[] = $file->getFilename();
            $fs->remove($file->getRealPath());
        }

        $this->assertTrue(\in_array('messages.en.yaml', $discoveredFiles));
    }
}
