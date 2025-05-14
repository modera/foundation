<?php

namespace Modera\TranslationsBundle\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\LanguagesBundle\Entity\Language;
use Modera\TranslationsBundle\Entity\LanguageTranslationToken;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AbstractFunctionalTestCase extends FunctionalTestCase
{
    private static SchemaTool $st;

    // override
    public static function setUpDatabase(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(Language::class),
            self::$em->getClassMetadata(TranslationToken::class),
            self::$em->getClassMetadata(LanguageTranslationToken::class),
        ]);
    }

    // override
    public static function dropDatabase(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(Language::class),
            self::$em->getClassMetadata(TranslationToken::class),
            self::$em->getClassMetadata(LanguageTranslationToken::class),
        ]);
    }

    protected function launchCompileCommand(array $parameters = []): void
    {
        $app = new Application(self::getContainer()->get('kernel'));
        $app->setAutoExit(false);

        $input = new ArrayInput(\array_merge([
            'command' => 'modera:translations:compile',
        ], $parameters));
        $input->setInteractive(false);

        $exitCode = $app->run($input, new NullOutput());

        $this->assertEquals(0, $exitCode);
    }

    protected function launchImportCommand(array $parameters = []): void
    {
        $app = new Application(self::getContainer()->get('kernel'));
        $app->setAutoExit(false);

        $input = new ArrayInput(\array_merge([
            'command' => 'modera:translations:import',
        ], $parameters));
        $input->setInteractive(false);

        $exitCode = $app->run($input, new NullOutput());

        $this->assertEquals(0, $exitCode);
    }
}
