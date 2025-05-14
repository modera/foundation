<?php

class ModeraBackendToolsActivityLogAppKernel extends Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Modera\ExpanderBundle\ModeraExpanderBundle(),
            new Modera\FoundationBundle\ModeraFoundationBundle(),

            new Modera\ActivityLoggerBundle\ModeraActivityLoggerBundle(),
            new Modera\BackendToolsActivityLogBundle\ModeraBackendToolsActivityLogBundle(),
            new Modera\SecurityBundle\ModeraSecurityBundle(),
            new Modera\ServerCrudBundle\ModeraServerCrudBundle(),
        ];
    }
}
