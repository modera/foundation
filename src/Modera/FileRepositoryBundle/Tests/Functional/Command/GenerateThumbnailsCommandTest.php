<?php

namespace Modera\FileRepositoryBundle\Tests\Functional\Command;

use Doctrine\ORM\Tools\SchemaTool;
use Imagine\Gd\Imagine;
use Modera\FileRepositoryBundle\Command\GenerateThumbnailsCommand;
use Modera\FileRepositoryBundle\Entity\Repository;
use Modera\FileRepositoryBundle\Entity\StoredFile;
use Modera\FileRepositoryBundle\Repository\FileRepository;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\Interceptor;
use Modera\FileRepositoryBundle\ThumbnailsGenerator\ThumbnailsGenerator;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\File\File;

class GenerateThumbnailsCommandTest extends FunctionalTestCase
{
    private static SchemaTool $st;

    private Application $application;

    private static FileRepository $fileRepository;

    private static ThumbnailsGenerator $generator;

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
        self::$fileRepository = self::getContainer()->get(FileRepository::class);

        self::$generator = self::getContainer()->get(ThumbnailsGenerator::class);

        $repositoryConfig = [
            'filesystem' => 'dummy_tmp_fs',
            'interceptors' => [
                Interceptor::class,
            ],
            'thumbnail_sizes' => [
                [
                    'width' => 215,
                    'height' => 285,
                ],
                [
                    'width' => 32,
                    'height' => 32,
                ],
            ],
        ];

        self::$fileRepository->createRepository('dummy_repo1', $repositoryConfig, 'Bla bla');

        $this->application = new Application(self::getContainer()->get('kernel'));
        $this->application->add(new GenerateThumbnailsCommand(self::$em, self::$fileRepository, self::$generator));
    }

    public function testExecuteNoThumbnailsToGenerate(): void
    {
        $commandName = 'modera:file-repository:generate-thumbnails';
        $command = $this->application->find('modera:file-repository:generate-thumbnails');

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $commandName,
            'repository' => 'dummy_repo1',
        ]);

        $this->assertEquals('No thumbnails to generate', \trim($commandTester->getDisplay()));
    }

    public function testExecuteDryRun(): void
    {
        $originalStoredFile = static::$fileRepository->put('dummy_repo1', new File(__DIR__.'/../../Fixtures/backend.png'));

        $commandName = 'modera:file-repository:generate-thumbnails';
        $command = $this->application->find('modera:file-repository:generate-thumbnails');

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $commandName,
            'repository' => 'dummy_repo1',
            '--thumbnail' => ['100x100', '50x50'],
            '--dry-run' => true,
        ]);

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

    public function testExecute(): void
    {
        $originalStoredFile1 = static::$fileRepository->put('dummy_repo1', new File(__DIR__.'/../../Fixtures/backend.png'));
        static::$fileRepository->put('dummy_repo1', new File(__DIR__.'/../../Fixtures/backend.png'));

        $commandName = 'modera:file-repository:generate-thumbnails';
        $command = $this->application->find('modera:file-repository:generate-thumbnails');

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $commandName,
            'repository' => 'dummy_repo1',
            '--thumbnail' => ['100x100', '50x50'],
        ]);

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
        $firstAlternatives = self::$em->getRepository(StoredFile::class)->findBy([
            'alternativeOf' => $originalStoredFile1->getId(),
        ]);

        $this->assertEquals(4, \count($firstAlternatives));

        $this->assertValidAlternative('backend.png', 100, 100, $firstAlternatives[2]);
        $this->assertValidAlternative('backend.png', 50, 50, $firstAlternatives[3]);

        $repoConfig = $repository->getConfig();

        $this->assertArrayHasKey('interceptors', $repoConfig);
        $this->assertTrue(\is_array($repoConfig['interceptors']));
        $this->assertTrue(false !== \array_search(Interceptor::class, $repoConfig['interceptors']));
        $this->assertArrayHasKey('thumbnail_sizes', $repoConfig);
        $this->assertTrue(\is_array($repoConfig['thumbnail_sizes']));
        $this->assertEquals(4, \count($repoConfig['thumbnail_sizes']));
        $this->assertEquals(['width' => 100, 'height' => 100], $repoConfig['thumbnail_sizes'][2]);
        $this->assertEquals(['width' => 50, 'height' => 50], $repoConfig['thumbnail_sizes'][3]);

        // now making sure that no duplicate alternatives are created:

        $commandTester->execute([
            'command' => $commandName,
            'repository' => 'dummy_repo1',
            '--thumbnail' => ['32x32', '50x50'],
        ]);

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

        $this->assertEquals(1, \count($repoConfig['interceptors']), 'No duplicate interceptors must have been registered.');
    }

    public function testExecuteNoConfigUpdate(): void
    {
        $repositoryConfig = [
            'filesystem' => 'dummy_tmp_fs',
        ];
        self::$fileRepository->createRepository('dummy_repo2', $repositoryConfig, 'Bla bla');

        $originalStoredFile1 = static::$fileRepository->put('dummy_repo2', new File(__DIR__.'/../../Fixtures/backend.png'));

        $commandName = 'modera:file-repository:generate-thumbnails';
        $command = $this->application->find('modera:file-repository:generate-thumbnails');

        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $commandName,
            'repository' => 'dummy_repo2',
            '--thumbnail' => ['100x100', '50x50'],
            '--update-config' => false,
        ]);

        $output = $commandTester->getDisplay();

        $expectedOutput = <<<'OUTPUT'
 # Processing (14) backend.png
  * 100x100
  * 50x50

OUTPUT;

        $this->assertEquals($expectedOutput, $output);

        /** @var StoredFile[] $alternatives */
        $alternatives = self::$em->getRepository(StoredFile::class)->findBy([
            'alternativeOf' => 14,
        ]);

        $this->assertEquals(2, \count($alternatives));

        $this->assertValidAlternative('backend.png', 100, 100, $alternatives[0]);
        $this->assertValidAlternative('backend.png', 50, 50, $alternatives[1]);

        /** @var Repository $repository */
        $repository = self::$em->getRepository(Repository::class)->find($originalStoredFile1->getRepository()->getId());
        $repoConfig = $repository->getConfig();

        $this->assertArrayNotHasKey('interceptors', $repoConfig);
        $this->assertArrayNotHasKey('thumbnail_sizes', $repoConfig);
    }

    private function assertValidAlternative($expectedName, $expectedWidth, $expectedHeight, StoredFile $storedFile): void
    {
        $this->assertEquals($expectedName, $storedFile->getFilename());
        $meta = $storedFile->getMeta();
        $this->assertArrayHasKey('thumbnail', $meta);
        $this->assertArrayHasKey('width', $meta['thumbnail']);
        $this->assertEquals($expectedWidth, $meta['thumbnail']['width']);
        $this->assertArrayHasKey('height', $meta['thumbnail']);
        $this->assertEquals($expectedHeight, $meta['thumbnail']['height']);

        $tmpFile = \tempnam(\sys_get_temp_dir(), 'image_');
        \file_put_contents($tmpFile, $storedFile->getContents());

        $imagine = new Imagine();
        $image = $imagine->open($tmpFile);

        $this->assertEquals($expectedWidth, $image->getSize()->getWidth());
    }
}
