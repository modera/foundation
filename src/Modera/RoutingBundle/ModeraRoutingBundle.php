<?php

namespace Modera\RoutingBundle;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Modera\RoutingBundle\DependencyInjection\DelegatingLoaderCloningCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2013 Modera Foundation
 */
class ModeraRoutingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new DelegatingLoaderCloningCompilerPass()
        );

        $routingResourcesProvider = new ExtensionPoint('modera_routing.routing_resources');
        $docs = <<<TEXT
This extension points make it possible for bundles to dynamically contribute routing resources so Symfony can detect them,
this way when a new bundle is added then you don't need to update root routing.yaml file every time.
This how a sample contribution could look like:

<?php

namespace Modera\ExampleBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;

#[AsContributorFor('modera_routing.routing_resources')]
class RoutingResourcesProvider implements ContributorInterface
{
    public function getItems()
    {
        return [
            '@ModeraExampleBundle/Resources/config/routing.yaml',
        ];
    }
}
TEXT;
        $routingResourcesProvider->setDetailedDescription($docs);
        $routingResourcesProvider->setDescription('Allows to dynamically add routing files.');
        $container->addCompilerPass($routingResourcesProvider->createCompilerPass());
    }
}
