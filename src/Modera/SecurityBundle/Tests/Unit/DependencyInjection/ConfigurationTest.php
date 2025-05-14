<?php

namespace Modera\SecurityBundle\Tests\Unit\DependencyInjection;

use Modera\SecurityBundle\DependencyInjection\Configuration;
use Modera\SecurityBundle\PasswordStrength\Mail\DefaultMailService;
use Modera\SecurityBundle\PasswordStrength\PasswordConfigInterface;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testPasswordStrength(): void
    {
        $configuration = new Configuration();

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, []);

        $this->assertArrayHasKey('password_strength', $config);

        $config = $config['password_strength'];
        $expectedConfig = [
            'mail' => [
                'service' => DefaultMailService::class,
            ],
            'enabled' => false,
            'min_length' => 6,
            'number_required' => false,
            'letter_required' => false,
            'rotation_period' => 90,
        ];
        $this->assertSame($expectedConfig, $config);
    }

    public function testPasswordStrengthLetterRequired()
    {
        $values = \array_merge(
            [true, false],
            PasswordConfigInterface::LETTER_REQUIRED_TYPES,
            ['on'],
        );

        foreach ($values as $value) {
            $this->assertPasswordStrengthLetterRequired($value);
        }
    }

    private function assertPasswordStrengthLetterRequired($value): void
    {
        $configuration = new Configuration();

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, [
            'modera_security' => [
                'password_strength' => [
                    'letter_required' => $value,
                ],
            ],
        ]);

        $expected = false;
        if (\is_bool($value) && $value) {
            $expected = PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL;
        } elseif (\is_string($value)) {
            $expected = $value;
            if (!\in_array($value, PasswordConfigInterface::LETTER_REQUIRED_TYPES)) {
                $expected = PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL;
            }
        }

        $expectedConfig = [
            'mail' => [
                'service' => DefaultMailService::class,
            ],
            'enabled' => false,
            'min_length' => 6,
            'number_required' => false,
            'letter_required' => $expected,
            'rotation_period' => 90,
        ];
        $this->assertEquals($expectedConfig, $config['password_strength']);
    }
}
