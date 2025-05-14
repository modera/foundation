<?php

class ModeraFoundationAppKernel extends Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Modera\FoundationBundle\ModeraFoundationBundle(),
        ];
    }
}
