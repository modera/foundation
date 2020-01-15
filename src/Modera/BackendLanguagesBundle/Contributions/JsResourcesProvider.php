<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei VIzel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('router');
        $this->defaultLocale = $container->getParameter('locale');
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array(
            $this->router->generate('modera_backend_languages_extjs_l10n', array('locale' => $this->defaultLocale)),
        );
    }
}
