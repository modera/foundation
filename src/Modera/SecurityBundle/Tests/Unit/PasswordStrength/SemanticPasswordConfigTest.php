<?php

namespace Modera\SecurityBundle\Tests\Unit\PasswordStrength;

use Modera\SecurityBundle\PasswordStrength\SemanticPasswordConfig;

class SemanticPasswordConfigTest extends \PHPUnit\Framework\TestCase
{
    private SemanticPasswordConfig $config;

    public function setUp(): void
    {
        $this->config = new SemanticPasswordConfig([
            'password_strength' => [
                'enabled' => true,
                'number_required' => true,
                'letter_required' => 'test',
                'rotation_period' => 123,
            ],
        ]);
    }

    public function testIsEnabled(): void
    {
        $this->assertTrue($this->config->isEnabled());
    }

    public function testNumberRequired(): void
    {
        $this->assertTrue($this->config->isNumberRequired());
    }

    public function testLetterRequired(): void
    {
        $this->assertTrue($this->config->isLetterRequired());
        $this->assertEquals('test', $this->config->getLetterRequiredType());
    }

    public function testRotationPeriod(): void
    {
        $this->assertEquals(123, $this->config->getRotationPeriodInDays());
    }
}
