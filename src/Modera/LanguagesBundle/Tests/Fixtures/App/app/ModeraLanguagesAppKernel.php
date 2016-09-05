<?php

class ModeraLanguagesAppKernel extends \Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Modera\LanguagesBundle\ModeraLanguagesBundle(),
            new Modera\LanguagesBundle\Tests\Fixtures\Bundle\ModeraLanguagesDummyBundle(),
        );
    }
}
