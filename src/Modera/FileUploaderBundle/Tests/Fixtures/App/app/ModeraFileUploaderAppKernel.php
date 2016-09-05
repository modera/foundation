<?php

class ModeraFileUploaderAppKernel extends \Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),

            new Modera\FileRepositoryBundle\ModeraFileRepositoryBundle(),

            new Modera\FileUploaderBundle\ModeraFileUploaderBundle(),
        );
    }
}
