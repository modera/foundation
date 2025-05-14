<?php

namespace Modera\MjrIntegrationBundle\Tests\Unit\Config;

use Modera\MjrIntegrationBundle\Config\CallbackConfigMerger;

class CallbackConfigMergerTest extends \PHPUnit\Framework\TestCase
{
    public function testHowWellItWorks(): void
    {
        $merger = new CallbackConfigMerger(function (array $input) {
            return \array_merge($input, [
                'another' => 'value',
            ]);
        });

        $result = $merger->merge(['foo' => 'bar']);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('bar', $result['foo']);
        $this->assertArrayHasKey('another', $result);
        $this->assertEquals('value', $result['another']);
    }
}
