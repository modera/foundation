<?php

namespace Modera\ActivityLoggerBundle\Tests\Unit\DependencyInjection;

use Modera\ActivityLoggerBundle\DependencyInjection\ModeraActivityLoggerExtension;
use Modera\ActivityLoggerBundle\DependencyInjection\ServiceAliasCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DummyContainerBuilder extends ContainerBuilder
{
}

class ServiceAliasCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess(): void
    {
        $builder = new ContainerBuilder();
        $builder->setParameter(ModeraActivityLoggerExtension::CONFIG_KEY, [
            'activity_manager' => 'some_service_id',
        ]);

        $this->assertFalse($builder->hasAlias('modera_activity_logger.manager.activity_manager'));

        $cp = new ServiceAliasCompilerPass();
        $cp->process($builder);

        $this->assertEquals('some_service_id', $builder->getAlias('modera_activity_logger.manager.activity_manager'));
    }
}
