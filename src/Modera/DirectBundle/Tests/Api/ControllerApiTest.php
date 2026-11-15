<?php

namespace Modera\DirectBundle\Tests\Api;

use Modera\DirectBundle\Api\ControllerApi;
use Modera\DirectBundle\Tests\Fixtures\MarkersController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ControllerApiTest extends TestCase
{
    private ControllerApi $api;

    public function setUp(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('getParameter')->willReturnMap([
            ['direct.api.remote_attribute', '@Remote'],
            ['direct.api.form_attribute', '@Form'],
            ['direct.api.safe_attribute', '@Secure'],
            ['direct.api.unsafe_attribute', '@Anonymous'],
        ]);

        $this->api = new ControllerApi($container, MarkersController::class);
    }

    public function testExposesAttributeAndDocblockMethods(): void
    {
        $byName = [];
        foreach ($this->api->getApi() ?? [] as $method) {
            $byName[$method['name']] = $method;
        }

        $this->assertSame(['name' => 'attribute', 'len' => 1, 'formHandler' => false], $byName['attribute']);
        $this->assertSame(['name' => 'attributeForm', 'len' => 2, 'formHandler' => true], $byName['attributeForm']);
        $this->assertSame(['name' => 'docblock', 'len' => 1, 'formHandler' => false], $byName['docblock']);
        $this->assertSame(['name' => 'docblockForm', 'len' => 2, 'formHandler' => true], $byName['docblockForm']);
        $this->assertArrayNotHasKey('hidden', $byName);
    }

    public function testMethodAccessFromAttributesAndDocblocks(): void
    {
        $this->assertSame('secure', $this->api->getMethodAccess('attributeSecureAction'));
        $this->assertSame('anonymous', $this->api->getMethodAccess('attributeAnonymousAction'));
        $this->assertSame('secure', $this->api->getMethodAccess('docblockSecureAction'));
        $this->assertSame('anonymous', $this->api->getMethodAccess('docblockAnonymousAction'));
        $this->assertSame('n', $this->api->getMethodAccess('attributeAction'));
    }
}
