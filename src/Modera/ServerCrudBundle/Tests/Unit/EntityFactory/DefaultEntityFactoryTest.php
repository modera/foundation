<?php

namespace Modera\ServerCrudBundle\Tests\Unit\EntityFactory;

use Modera\ServerCrudBundle\EntityFactory\DefaultEntityFactory;

class DummyClassWithNoMandatoryArgumentsConstructor
{
    public string $arg1;

    public function __construct(string $arg1 = 'default-value')
    {
        $this->arg1 = $arg1;
    }
}

class DummyClassWithMandatoryConstructorArgs
{
    public string $arg1 = 'foo';

    public function __construct(string $arg1)
    {
        $this->arg1 = $arg1;
    }
}

class DefaultEntityFactoryTest extends \PHPUnit\Framework\TestCase
{
    private DefaultEntityFactory $factory;

    public function setUp(): void
    {
        $this->factory = new DefaultEntityFactory();
    }

    public function testCreateWithoutConstructor(): void
    {
        $object = $this->factory->create([], ['entity' => 'stdClass']);

        $this->assertInstanceOf('stdClass', $object);
    }

    public function testCreateWithConstructorWithNoMandatoryParameters(): void
    {
        /** @var DummyClassWithNoMandatoryArgumentsConstructor $object */
        $object = $this->factory->create([], ['entity' => DummyClassWithNoMandatoryArgumentsConstructor::class]);

        $this->assertInstanceOf(DummyClassWithNoMandatoryArgumentsConstructor::class, $object);
        $this->assertEquals('default-value', $object->arg1);
    }

    public function testCreateWithConstructorWithMandatoryParameters(): void
    {
        /** @var DummyClassWithMandatoryConstructorArgs $object */
        $object = $this->factory->create([], ['entity' => DummyClassWithMandatoryConstructorArgs::class]);

        $this->assertInstanceOf(DummyClassWithMandatoryConstructorArgs::class, $object);
        $this->assertEquals('foo', $object->arg1);
    }
}
