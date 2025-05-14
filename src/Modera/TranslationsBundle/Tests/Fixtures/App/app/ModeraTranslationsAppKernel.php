<?php

class ModeraTranslationsAppKernel extends Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Modera\LanguagesBundle\ModeraLanguagesBundle(),
            new Modera\TranslationsBundle\ModeraTranslationsBundle(),
            new Modera\TranslationsBundle\Tests\Fixtures\Bundle\ModeraTranslationsDummyBundle(),
            new Modera\TranslationsBundle\Tests\Fixtures\SecondBundle\ModeraTranslationsSecondDummyBundle(),
        ];
    }
}
