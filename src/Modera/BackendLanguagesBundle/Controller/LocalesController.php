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

        $locales = array_filter($this->getLocales(), function($locale) use ($params) {
            $parts = explode('_', $locale);
            if (count($parts) > 1) {
                if (isset($params['ignore']) && is_array($params['ignore']) && in_array($locale, $params['ignore'])) {
                    return false;
                }
                return isset($params['language']) ? $params['language'] == $parts[0] : true;
            }
            return false;
        });

        $arr = array();
        foreach ($locales as $locale) {
            $value = Language::getLocaleName($locale, $this->getDisplayLocale());
            if (isset($params['language'])) {
                $value = substr(explode('(', $value, 2)[1], 0, -1);
            }
            $arr[$locale] = $value;
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
     * @return array
     */
    private function getLocales()
    {
        $locales = array_keys(Intl::getLocaleBundle()->getLocaleNames());
        foreach ($this->get('modera_backend_languages.locales_provider')->getItems() as $locale) {
            $locales[] = $locale;
        }
        return $locales;
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
