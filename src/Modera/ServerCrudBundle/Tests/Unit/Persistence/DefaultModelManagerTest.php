<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Persistence;

use Modera\ServerCrudBundle\Persistence\DefaultModelManager;

class DefaultModelManagerTest extends \PHPUnit\Framework\TestCase
{
    private DefaultModelManager $mgr;

    public function setUp(): void
    {
        $this->mgr = new DefaultModelManager();
    }

    public function testGenerateModelIdFromEntityClass(): void
    {
        $this->assertEquals(
            'modera.admin_generator.foo',
            $this->mgr->generateModelIdFromEntityClass('Modera\AdminGenerator\Entity\Foo')
        );

        $this->assertEquals(
            'modera.admin_generator.sub.bar',
            $this->mgr->generateModelIdFromEntityClass('Modera\AdminGenerator\Entity\Sub\Bar')
        );
    }

    public function testGenerateEntityClassFromModelId(): void
    {
        $this->assertEquals(
            'Modera\AdminGeneratorBundle\Entity\Foo',
            $this->mgr->generateEntityClassFromModelId('modera.admin_generator.foo')
        );

        $this->assertEquals(
            'Modera\AdminGeneratorBundle\Entity\Sub\Bar',
            $this->mgr->generateEntityClassFromModelId('modera.admin_generator.sub.bar')
        );
    }
}
