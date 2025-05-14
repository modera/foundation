<?php

namespace Modera\SecurityBundle\DependencyInjection;

use Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @copyright 2017 Modera Foundation
 */
class MailServiceAliasCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array{'password_strength': array{'mail': array{'service': string}}} $config */
        $config = $container->getParameter(ModeraSecurityExtension::CONFIG_KEY);

        $container->setAlias(MailServiceInterface::class, $config['password_strength']['mail']['service']);
    }
}
