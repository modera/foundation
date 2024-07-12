<?php

namespace Modera\TranslationsBundle\Tests\Functional\Compiler;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Modera\TranslationsBundle\Tests\Functional\AbstractFunctionalTestCase;
use Modera\TranslationsBundle\Compiler\TranslationsCompiler;

/**
 * Smoke test.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
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

    public function testCompile()
    {
        /*$this->markTestSkipped(
            'After migration to Symfony 3.1+ this test started exploding with "PDOException: There is no active transaction"'
        );*/
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->getConnection()->beginTransaction();

        /* @var KernelInterface $kernel */
        $kernel = self::getContainer()->get('kernel');

        /* @var TranslationsCompiler $compiler */
        $compiler = self::getContainer()->get('modera_translations.compiler.translations_compiler');

        $this->launchImportCommand();

        $compiler->compile();

        $translationsDir = $kernel->getProjectDir().'/app/Resources/translations';

        $fs = new Filesystem();
        $discoveredFiles = array();
        foreach (Finder::create()->in($translationsDir) as $file) {
            $discoveredFiles[] = $file->getFilename();
            $fs->remove($file->getRealPath());
        }

        $this->assertTrue(in_array('messages.en.yml', $discoveredFiles));
    }
}
