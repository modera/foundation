<?php

namespace Modera\ActivityLoggerBundle\Tests\Unit;

use Modera\ActivityLoggerBundle\DependencyInjection\ServiceAliasCompilerPass;
use Modera\ActivityLoggerBundle\ModeraActivityLoggerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModeraActivityLoggerBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild(): void
    {
        $bundle = new ModeraActivityLoggerBundle();

        $builder = new ContainerBuilder();
        $bundle->build($builder);

        $passes = $builder->getCompiler()->getPassConfig()->getPasses();

        $hasPass = false;
        foreach ($passes as $pass) {
            if ($pass instanceof ServiceAliasCompilerPass) {
                $hasPass = true;
                break;
            }
        }

        $this->assertTrue($hasPass);
    }
}
