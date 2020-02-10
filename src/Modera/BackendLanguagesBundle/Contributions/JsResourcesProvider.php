<?php

namespace Modera\BackendLanguagesBundle\Contributions;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\LanguagesBundle\Entity\Language;

/**
 * @author    Sergei VIzel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class JsResourcesProvider implements ContributorInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param EntityManager $em
     * @param Router $router
     * @param string $defaultLocale
     */
    public function __construct(EntityManager $em, Router $router, $defaultLocale = 'en')
    {
        $this->em = $em;
        $this->router = $router;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $locale = $this->defaultLocale;

        /* @var Language $defaultLanguage */
        $defaultLanguage = $this->em->getRepository(Language::clazz())->findOneBy(array(
            'isDefault' => true,
        ));
        if ($defaultLanguage) {
            $locale = $defaultLanguage->getLocale();
        }

        return array(
            $this->router->generate('modera_backend_languages_extjs_l10n', array('locale' => $locale)),
        );
    }
}
