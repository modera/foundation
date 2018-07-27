<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Symfony\Component\Intl\Intl;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
use Modera\FoundationBundle\Controller\AbstractBaseController;
use Modera\LanguagesBundle\Entity\Language;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2018 Modera Foundation
 */
class LocalesController extends AbstractBaseController
{
    protected function checkAccess()
    {
        $role = ModeraMJRSecurityIntegrationBundle::ROLE_BACKEND_USER;
        if (false === $this->get('security.authorization_checker')->isGranted($role)) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @Remote
     *
     * @param array $params
     * @return array
     */
    public function listAction(array $params)
    {
        $this->checkAccess();

        $arr = Intl::getLocaleBundle()->getLocaleNames();
        foreach ($arr as $locale => $name) {
            $arr[$locale] = Language::getLocaleName($locale, $this->getDisplayLocale());
        }

        // add custom locales
        foreach ($this->get('modera_backend_languages.locales_provider')->getItems() as $locale) {
            $arr[$locale] = Language::getLocaleName($locale, $this->getDisplayLocale());
        }

        $collator = new \Collator($this->getDisplayLocale());
        $collator->asort($arr);

        $result = array();
        foreach ($arr as $locale => $name) {
            $result[] = array(
                'id' => $locale,
                'name' => $name,
            );
        }

        return array(
            'success' => true,
            'items' => $result,
            'total' => count($result),
        );
    }

    /**
     * @return string
     */
    private function getDisplayLocale()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        return $request->getLocale();
    }
}
