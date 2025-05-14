<?php

namespace Modera\FileUploaderBundle\Tests\Functional\DependencyInjection;

use Modera\FileUploaderBundle\DependencyInjection\ModeraFileUploaderExtension;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraFileUploaderExtensionTest extends FunctionalTestCase
{
    private ModeraFileUploaderExtension $ext;

    private ContainerBuilder $cb;

    public function doSetUp(): void
    {
        $this->ext = new ModeraFileUploaderExtension();
        $this->cb = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $this->ext->load([], $this->cb);

        $cfg = $this->cb->getParameter(ModeraFileUploaderExtension::CONFIG_KEY);
        $this->assertTrue(\is_array($cfg));
        $this->assertArrayHasKey('is_enabled', $cfg);
        $this->assertArrayHasKey('url', $cfg);
        $this->assertArrayHasKey('expose_all_repositories', $cfg);
        $this->assertTrue($cfg['expose_all_repositories']);

        $gateway = $this->cb->getDefinition('modera_file_uploader.uploading.all_exposed_repositories_gateway');
        $this->assertNotNull($gateway);

        $provider = $this->cb->getDefinition('modera_file_uploader.uploading.all_exposed_repositories_gateway_provider');
        $this->assertNotNull($provider);
    }

    public function testLoadWhenRepositoriesAreNotExposed(): void
    {
        $config = [
            [
                'expose_all_repositories' => false,
            ],
        ];

        $this->ext->load($config, $this->cb);

        $this->assertFalse(
            $this->cb->hasDefinition('modera_file_uploader.uploading.all_exposed_repositories_gateway')
        );
        $this->assertFalse(
            $this->cb->hasDefinition('modera_file_uploader.uploading.all_exposed_repositories_gateway_provider')
        );
    }
}
