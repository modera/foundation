<?php

namespace Modera\FileRepositoryBundle\Tests\Functional\Command;

use Imagine\Gd\Imagine;
use Doctrine\ORM\Tools\SchemaTool;
use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Modera\FileRepositoryBundle\Repository\FileRepository;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\Interceptor;
use Modera\FileRepositoryBundle\Command\GenerateThumbnailsCommand;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\ThumbnailsGenerator;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class GenerateThumbnailsCommandTest extends FunctionalTestCase
{
    private static $st;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var FileRepository
     */
    private static $fileRepository;

    /**
     * @var ThumbnailsGenerator
     */
    private static $generator;

    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(Repository::class),
            self::$em->getClassMetadata(StoredFile::class),
        ]);
    }

    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(Repository::class),
            self::$em->getClassMetadata(StoredFile::class),
        ]);
    }

    public function doSetUp(): void
    {
        /* @var FileRepository $fr */
        self::$fileRepository = self::getContainer()->get('modera_file_repository.repository.file_repository');

        /* @var ThumbnailsGenerator $generator */
        self::$generator = self::getContainer()->get('modera_file_repository.interceptors.thumbnails_generator.thumbnails_generator');

        $repositoryConfig = array(
            'filesystem' => 'dummy_tmp_fs',
            'interceptors' => [
                Interceptor::ID,
            ],
            'thumbnail_sizes' => array(
                array(
                    'width' => 215,
                    'height' => 285,
                ),
                array(
                    'width' => 32,
                    'height' => 32,
                ),
            ),
        );

        self::$fileRepository->createRepository('dummy_repo1', $repositoryConfig, 'Bla bla');

        $this->application = new Application(self::getContainer()->get('kernel'));
        $this->application->add(new GenerateThumbnailsCommand(self::$em, self::$fileRepository, self::$generator));
    }

    public function testExecute_noThumbnailsToGenerate()
    {
        $commandName = 'modera:file-repository:generate-thumbnails';
        $command = $this->application->find('modera:file-repository:generate-thumbnails');

        $commandTester = new CommandTester($command);

        $commandTester->execute(array(
            'command' => $commandName,
            'repository' => 'dummy_repo1',
        ));

        $this->assertContains('No thumbnails to generate', $commandTester->getDisplay());
    }

    public function testExecute_dryRun()
    {
        $originalStoredFile = static::$fileRepository->put('dummy_repo1', new File(__DIR__.'/../../Fixtures/backend.png'));

        $commandName = 'modera:file-repository:generate-thumbnails';
        $command = $this->application->find('modera:file-repository:generate-thumbnails');

        $commandTester = new CommandTester($command);

        $commandTester->execute(array(
            'command' => $commandName,
            'repository' => 'dummy_repo1',
            '--thumbnail' => ['100x100', '50x50'],
            '--dry-run' => true,
        ));

        $output = $commandTester->getDisplay();

        $expectedOutput = <<<'OUTPUT'
+----+-------------+--------------------+---------------------+
| ID | Filename    | Missing thumbnails | Existing thumbnails |
+----+-------------+--------------------+---------------------+
| {0}  | backend.png | 100x100, 50x50     | 32x32, 215x285      |
+----+-------------+--------------------+---------------------+

OUTPUT;
        $expectedOutput = \str_replace('{0}', $originalStoredFile->getId(), $expectedOutput);

        $this->assertEquals($expectedOutput, $output);
    }

    public function testExecute()
    {
        $originalStoredFile1 = static::$fileRepository->put('dummy_repo1', new File(__DIR__.'/../../Fixtures/backend.png'));
        static::$fileRepository->put('dummy_repo1', new File(__DIR__.'/../../Fixtures/backend.png'));

        $commandName = 'modera:file-repository:generate-thumbnails';
        $command = $this->application->find('modera:file-repository:generate-thumbnails');

        $commandTester = new CommandTester($command);

        $commandTester->execute(array(
            'command' => $commandName,
            'repository' => 'dummy_repo1',
            '--thumbnail' => ['100x100', '50x50'],
        ));

        $output = $commandTester->getDisplay();

        /** @var Repository $repository */
        $repository = self::$em->getRepository(Repository::class)->find($originalStoredFile1->getRepository()->getId());

        $ids = [];
        foreach ($repository->getFiles() as $file) {
            if (!$file->getAlternativeOf()) {
                $ids[] = $file->getId();
            }
        }

        $expectedOutput = <<<'OUTPUT'
 # Processing ({0}) backend.png
  * 100x100
  * 50x50
 # Processing ({1}) backend.png
  * 100x100
  * 50x50
Interceptor is already has been registered before, skipping ...
Thumbnails config updated for repository

OUTPUT;
        $expectedOutput = \str_replace(['{0}', '{1}'], $ids, $expectedOutput);

        $this->assertEquals($expectedOutput, $output);

        /** @var StoredFile[] $firstAlternatives */
        $firstAlternatives = self::$em->getRepository(StoredFile::class)->findBy(array(
            'alternativeOf' => $originalStoredFile1->getId(),
        ));

        $this->assertEquals(4, count($firstAlternatives));

        $this->assertValidAlternative('backend.png', 100, 100, $firstAlternatives[2]);
        $this->assertValidAlternative('backend.png', 50, 50, $firstAlternatives[3]);

        $repoConfig = $repository->getConfig();

        $this->assertArrayHasKey('interceptors', $repoConfig);
        $this->assertTrue(is_array($repoConfig['interceptors']));
        $this->assertTrue(false !== array_search(Interceptor::ID, $repoConfig['interceptors']));
        $this->assertArrayHasKey('thumbnail_sizes', $repoConfig);
        $this->assertTrue(is_array($repoConfig['thumbnail_sizes']));
        $this->assertEquals(4, count($repoConfig['thumbnail_sizes']));
        $this->assertEquals(array('width' => 100, 'height' => 100), $repoConfig['thumbnail_sizes'][2]);
        $this->assertEquals(array('width' => 50, 'height' => 50), $repoConfig['thumbnail_sizes'][3]);

        // now making sure that no duplicate alternatives are created:

        $commandTester->execute(array(
            'command' => $commandName,
            'repository' => 'dummy_repo1',
            '--thumbnail' => ['32x32', '50x50'],
        ));

        $secondOutput = $commandTester->getDisplay();

        $expectedSecondOutput = <<<'OUTPUT'
 # Processing ({0}) backend.png
 # Processing ({1}) backend.png
Interceptor is already has been registered before, skipping ...
Repository already contains necessary thumbnails config, skipping ...

OUTPUT;
        $expectedSecondOutput = \str_replace(['{0}', '{1}'], $ids, $expectedSecondOutput);

        // 32x32 thumbnails must not have been generated again
        $this->assertEquals($secondOutput, $expectedSecondOutput);

        self::$em->clear();

        /** @var Repository $repository */
        $repository = self::$em->getRepository(Repository::class)->find($originalStoredFile1->getRepository()->getId());
        $repoConfig = $repository->getConfig();

        $this->assertEquals(1, count($repoConfig['interceptors']), 'No duplicate interceptors must have been registered.');
    }

    public function testExecute_noConfigUpdate()
    {
        $repositoryConfig = array(
            'filesystem' => 'dummy_tmp_fs',
        );
        self::$fileRepository->createRepository('dummy_repo2', $repositoryConfig, 'Bla bla');

        $originalStoredFile1 = static::$fileRepository->put('dummy_repo2', new File(__DIR__.'/../../Fixtures/backend.png'));

        $commandName = 'modera:file-repository:generate-thumbnails';
        $command = $this->application->find('modera:file-repository:generate-thumbnails');

        $commandTester = new CommandTester($command);

        $commandTester->execute(array(
            'command' => $commandName,
            'repository' => 'dummy_repo2',
            '--thumbnail' => ['100x100', '50x50'],
            '--update-config' => false,
        ));

        $output = $commandTester->getDisplay();

        $expectedOutput = <<<'OUTPUT'
 # Processing (14) backend.png
  * 100x100
  * 50x50

OUTPUT;

        $this->assertEquals($expectedOutput, $output);

        /* @var StoredFile[] $alternatives */
        $alternatives = self::$em->getRepository(StoredFile::class)->findBy(array(
            'alternativeOf' => 14,
        ));

        $this->assertEquals(2, count($alternatives));

        $this->assertValidAlternative('backend.png', 100, 100, $alternatives[0]);
        $this->assertValidAlternative('backend.png', 50, 50, $alternatives[1]);

        /* @var Repository $repository */
        $repository = self::$em->getRepository(Repository::class)->find($originalStoredFile1->getRepository()->getId());
        $repoConfig = $repository->getConfig();

        $this->assertArrayNotHasKey('interceptors', $repoConfig);
        $this->assertArrayNotHasKey('thumbnail_sizes', $repoConfig);
    }

    private function assertValidAlternative($expectedName, $expectedWidth, $expectedHeight, StoredFile $storedFile)
    {
        $this->assertEquals($expectedName, $storedFile->getFilename());
        $meta = $storedFile->getMeta();
        $this->assertArrayHasKey('thumbnail', $meta);
        $this->assertArrayHasKey('width', $meta['thumbnail']);
        $this->assertEquals($expectedWidth, $meta['thumbnail']['width']);
        $this->assertArrayHasKey('height', $meta['thumbnail']);
        $this->assertEquals($expectedHeight, $meta['thumbnail']['height']);

        $tmpFile = tempnam(sys_get_temp_dir(), 'image_');
        file_put_contents($tmpFile, $storedFile->getContents());

        $imagine = new Imagine();
        $image = $imagine->open($tmpFile);

        $this->assertEquals($expectedWidth, $image->getSize()->getWidth());
    }
}
