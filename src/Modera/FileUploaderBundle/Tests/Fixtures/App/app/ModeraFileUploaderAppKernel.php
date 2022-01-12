<?php

class ModeraFileUploaderAppKernel extends \Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Modera\FileRepositoryBundle\ModeraFileRepositoryBundle(),

            new Modera\FileUploaderBundle\ModeraFileUploaderBundle(),
        );
    }
}
