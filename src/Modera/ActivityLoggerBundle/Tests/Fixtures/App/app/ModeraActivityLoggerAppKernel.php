<?php

class ModeraActivityLoggerAppKernel extends Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Modera\ActivityLoggerBundle\ModeraActivityLoggerBundle(),
            new Modera\ServerCrudBundle\ModeraServerCrudBundle(),
        ];
    }
}
