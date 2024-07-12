<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
{
    private Router $router;

    public function __construct(ContainerInterface $container)
    {
        /** @var Router $router */
        $router = $container->get('router');
        $this->router = $router;
    }

    public function getItems(): array
    {
        return \array_merge(FontAwesome::cssResources(), [
            $this->router->generate('modera_font_awesome_css'),
        ]);
    }
}
