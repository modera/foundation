<?php

namespace Modera\ServerCrudBundle\Tests\Unit\NewValuesFactory;

use Modera\ServerCrudBundle\NewValuesFactory\DefaultNewValuesFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DummyEntity
{
}

class AnotherDummyEntity
{
    public static function formatNewValues(array $params, array $config): array
    {
        return [
            'params' => $params,
            'config' => $config,
        ];
    }
}

class DefaultNewValuesFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetValues(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $nvf = new DefaultNewValuesFactory($container);

        $inputParams = ['input-params'];
        $inputConfig = ['entity' => DummyEntity::class];

        $this->assertSame([], $nvf->getValues($inputParams, $inputConfig));

        // ---

        $inputConfig = ['entity' => AnotherDummyEntity::class];

        $expectedResult = [
            'params' => $inputParams,
            'config' => $inputConfig,
        ];

        $this->assertSame($expectedResult, $nvf->getValues($inputParams, $inputConfig));
    }
}
