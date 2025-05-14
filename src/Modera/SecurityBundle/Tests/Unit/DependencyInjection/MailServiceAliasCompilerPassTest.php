<?php

namespace Modera\SecurityBundle\Tests\Unit\DependencyInjection;

use Modera\SecurityBundle\DependencyInjection\MailServiceAliasCompilerPass;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MailServiceAliasCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter(
            ModeraSecurityExtension::CONFIG_KEY,
            [
                'password_strength' => [
                    'mail' => [
                        'service' => 'foo_service',
                    ],
                ],
            ],
        );

        $compilerPass = new MailServiceAliasCompilerPass();
        $compilerPass->process($container);

        $this->assertEquals(
            'foo_service',
            (string) $container->getAlias(MailServiceInterface::class),
        );
    }
}
