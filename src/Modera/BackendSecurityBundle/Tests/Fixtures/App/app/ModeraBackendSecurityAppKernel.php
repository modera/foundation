<?php

class ModeraBackendSecurityAppKernel extends Modera\FoundationBundle\Testing\AbstractFunctionalKernel
{
    public function registerBundles(): iterable
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new Modera\ExpanderBundle\ModeraExpanderBundle(),

            new Modera\TranslationsBundle\ModeraTranslationsBundle(),
            new Modera\FoundationBundle\ModeraFoundationBundle(),
            new Modera\MjrIntegrationBundle\ModeraMjrIntegrationBundle(),
            new Modera\DirectBundle\ModeraDirectBundle(),
            new Modera\SecurityBundle\ModeraSecurityBundle(),
            new Modera\BackendToolsBundle\ModeraBackendToolsBundle(),
            new Modera\ActivityLoggerBundle\ModeraActivityLoggerBundle(),
            new Modera\BackendTranslationsToolBundle\ModeraBackendTranslationsToolBundle(),
            new Modera\ServerCrudBundle\ModeraServerCrudBundle(),
            new Modera\BackendSecurityBundle\ModeraBackendSecurityBundle(),
        ];
    }
}
