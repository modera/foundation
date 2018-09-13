<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Doctrine\ORM\EntityManager;
use Modera\LanguagesBundle\Entity\Language;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Modera\SecurityBundle\Entity\User;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $currentConfig
     *
     * @return array
     */
    public function merge(array $currentConfig)
    {
        $languages = array();
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /* @var Language[] $dbLanguages */
        $dbLanguages = $em->getRepository(Language::clazz())->findBy(array('isEnabled' => true));
        foreach ($dbLanguages as $dbLanguage) {
            $languages[] = array(
                'id' => $dbLanguage->getId(),
                'name' => $dbLanguage->getName(),
                'locale' => $dbLanguage->getLocale(),
            );
        }

        //get user localisation files
        /* @var RequestStack */
        $requestStack = $this->container->get('request_stack');
        /* @var Request $request */
        $request = $requestStack->getCurrentRequest();
        /* @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        $token = $tokenStorage->getToken();
        $runtimeConfig = $this->container->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);
        /* @var Router $router */
        $router = $this->container->get('router');

        $pluginUrls = [];

        if ($token->isAuthenticated() && $token->getUser() instanceof User) {
            /* @var EntityManager $em */
            $em = $this->container->get('doctrine.orm.entity_manager');
            /* @var UserSettings $settings */
            $settings = $em->getRepository(UserSettings::clazz())->findOneBy(array('user' => $token->getUser()->getId()));
            if ($settings && $settings->getLanguage() && $settings->getLanguage()->getEnabled()) {
                $userLocale = $settings->getLanguage()->getLocale();

                $pluginUrls[] = $runtimeConfig['extjs_path'].'/locale/ext-lang-'.$userLocale.'.js';
                $pluginUrls[] = $router->generate('modera_backend_languages_extjs_l10n', array('locale' => $userLocale));
            }
        }

        return array_merge($currentConfig, array(
            'modera_backend_languages' => array(
                'languages'         => $languages,
                'localization_urls' => $pluginUrls
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array($this);
    }
}
