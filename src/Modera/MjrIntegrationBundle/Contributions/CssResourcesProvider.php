<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CssResourcesProvider implements ContributorInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('router');
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array_merge(FontAwesome::cssResources(), array(
            $this->router->generate('modera_font_awesome_css'),
        ));
    }
}
