<?php

namespace Modera\ConfigBundle\Tests\Unit\Twig;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\ConfigBundle\Twig\TwigExtension;
use Twig\TwigFunction;

class TwigExtensionTest extends \PHPUnit\Framework\TestCase
{
    private TwigExtension $ext;

    private ConfigurationEntriesManagerInterface $configEntriesManager;

    public function setUp(): void
    {
        $this->configEntriesManager = \Phake::mock(ConfigurationEntriesManagerInterface::class);

        $this->ext = new TwigExtension($this->configEntriesManager);
    }

    public function testGetFunctions(): void
    {
        /** @var TwigFunction[] $functions */
        $functions = $this->ext->getFunctions();

        $this->assertEquals(2, \count($functions));
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);

        $configValue = $functions[0];

        $this->assertEquals('modera_config_value', $configValue->getName());

        $callable = $configValue->getCallable();
        $this->assertSame($this->ext, $callable[0]);
        $this->assertEquals('twigModeraConfigValue', $callable[1]);

        $configOwnerValue = $functions[1];

        $callable = $configOwnerValue->getCallable();
        $this->assertSame($this->ext, $callable[0]);
        $this->assertEquals('getModeraConfigOwnerValue', $callable[1]);
    }

    public function testTwigModeraConfigValueNotStrict(): void
    {
        $value = $this->ext->twigModeraConfigValue('fooproperty', false);

        $this->assertNull($value);

        \Phake::verify($this->configEntriesManager)
            ->findOneByName('fooproperty', null)
        ;

        // ---

        $property = \Phake::mock('Modera\ConfigBundle\Config\ConfigurationEntryInterface');
        \Phake::when($property)
            ->getValue()
            ->thenReturn('barvalue')
        ;

        \Phake::when($this->configEntriesManager)
            ->findOneByName('barproperty', null)
            ->thenReturn($property)
        ;

        $this->assertEquals('barvalue', $this->ext->twigModeraConfigValue('barproperty', false));
    }

    public function testTwigModeraConfigValueStrict(): void
    {
        $this->expectException(\RuntimeException::class);
        \Phake::when($this->configEntriesManager)
            ->findOneByNameOrDie('foo', null)
            ->thenThrow(new \RuntimeException('ololo'))
        ;

        $this->ext->twigModeraConfigValue('foo');
    }
}
