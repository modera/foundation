<?php

namespace Modera\BackendSecurityBundle\Tests\Unit\Contributions;

use Modera\BackendSecurityBundle\Contributions\ConfigMergersProvider;
use Modera\BackendSecurityBundle\Tests\Fixtures\App\Contributions\ClientDIContributor;
use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\ExpanderBundle\Ext\ExtensionPointManager;
use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigMergersProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testMerge(): void
    {
        $container = \Phake::mock(ContainerInterface::class);
        \Phake::when($container)
            ->has('modera_backend_security.sections_provider')
            ->thenReturn(true)
        ;
        \Phake::when($container)
            ->get('modera_backend_security.sections_provider')
            ->thenReturn(new ClientDIContributor())
        ;

        $extensionPointManager = \Phake::mock(ExtensionPointManager::class);
        \Phake::when($extensionPointManager)
            ->has('modera_backend_security.sections')
            ->thenReturn(true)
        ;
        \Phake::when($extensionPointManager)
            ->get('modera_backend_security.sections')
            ->thenReturn(new ExtensionPoint('modera_backend_security.sections'))
        ;

        $provider = new ConfigMergersProvider(
            new ExtensionProvider($container, $extensionPointManager),
            [
                'hide_delete_user_functionality' => true,
            ],
        );

        $actualResult = $provider->merge([
            'existing_key' => 'foobar',
        ]);
        $expectedResult = [
            'existing_key' => 'foobar',
            'modera_backend_security' => [
                'hideDeleteUserFunctionality' => true,
                'sections' => [
                    [
                        'sectionConfig' => [
                            'name' => 'section1',
                            'uiClass' => 'Some.ui.class',
                        ],
                        'menuConfig' => [
                            'itemId' => 'section1',
                            'text' => 'Section 1',
                            'glyph' => 'icon-1',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expectedResult, $actualResult);
    }
}
