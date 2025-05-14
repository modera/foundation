<?php

namespace Modera\LanguagesBundle\Tests\Functional\Command;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\LanguagesBundle\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class SyncLanguagesCommandTest extends FunctionalTestCase
{
    private static SchemaTool $st;

    // override
    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(Language::class),
        ]);
    }

    // override
    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(Language::class),
        ]);
    }

    private function launchCommand(array|string|null $config = null): void
    {
        $app = new Application(self::$kernel->getContainer()->get('kernel'));
        $app->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'modera:languages:config-sync-dummy',
            'config' => $config ? \json_encode($config) : null,
        ]);
        $input->setInteractive(false);

        $result = $app->run($input, new NullOutput());

        $this->assertEquals(0, $result);
    }

    private function checkCount($expected): void
    {
        $dbLanguages = self::$em->getRepository(Language::class)->findAll();
        $this->assertEquals($expected, count($dbLanguages));
    }

    private function checkEnabled($expected): void
    {
        $actual = 0;
        $dbLanguages = self::$em->getRepository(Language::class)->findAll();
        foreach ($dbLanguages as $dbLanguage) {
            if ($dbLanguage->isEnabled()) {
                ++$actual;
            }
        }
        $this->assertEquals($expected, $actual);
    }

    public function testEmptyConfig(): void
    {
        $this->checkCount(0);
        $this->launchCommand();
        $this->checkCount(0);
    }

    public function testConfigFile(): void
    {
        $this->checkCount(0);
        $this->launchCommand('config-file');
        $this->checkCount(3);
        $this->checkEnabled(2);
    }

    public function testSync(): void
    {
        $this->checkCount(0);

        $this->launchCommand([
            [
                'locale' => 'en',
                'is_enabled' => true,
            ],
        ]);
        $this->checkCount(1);
        $this->checkEnabled(1);

        $this->launchCommand([
            [
                'locale' => 'en',
                'is_enabled' => false,
            ],
        ]);
        $this->checkCount(1);
        $this->checkEnabled(0);

        $this->launchCommand([
            [
                'locale' => 'en',
                'is_enabled' => true,
            ],
            [
                'locale' => 'ru',
                'is_enabled' => true,
            ],
        ]);
        $this->checkCount(2);
        $this->checkEnabled(2);

        $this->launchCommand([
            [
                'locale' => 'en',
                'is_enabled' => false,
            ],
            [
                'locale' => 'ru',
                'is_enabled' => true,
            ],
        ]);
        $this->checkCount(2);
        $this->checkEnabled(1);

        $this->launchCommand([
            [
                'locale' => 'en',
                'is_enabled' => true,
            ],
            [
                'locale' => 'ru',
                'is_enabled' => false,
            ],
        ]);
        $this->checkCount(2);
        $this->checkEnabled(1);

        $this->launchCommand([
            [
                'locale' => 'ru',
                'is_enabled' => true,
            ],
        ]);
        $this->checkCount(2);
        $this->checkEnabled(1);

        $this->launchCommand([
            [
                'locale' => 'ru',
                'is_enabled' => false,
            ],
        ]);
        $this->checkCount(2);
        $this->checkEnabled(0);

        $this->launchCommand([
            [
                'locale' => 'en',
                'is_enabled' => true,
            ],
            [
                'locale' => 'ru',
                'is_enabled' => true,
            ],
        ]);
        $this->checkCount(2);
        $this->checkEnabled(2);

        $this->launchCommand();
        $this->checkCount(2);
        $this->checkEnabled(0);
    }
}
