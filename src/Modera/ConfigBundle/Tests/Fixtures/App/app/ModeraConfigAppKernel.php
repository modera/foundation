<?php

class ModeraConfigAppKernel extends Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Modera\ExpanderBundle\ModeraExpanderBundle(),
            new Modera\ConfigBundle\ModeraConfigBundle(),
        ];
    }
}
