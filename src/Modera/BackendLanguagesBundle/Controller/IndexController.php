<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Modera\LanguagesBundle\Helper\LocaleHelper;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class IndexController extends Controller
{
    /**
     * @return string
     */
    protected function getDomain()
    {
        return 'extjs';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplate()
    {
        return 'ModeraBackendLanguagesBundle:Index:compile.js.twig';
    }

    /**
     * @param Request $request
     * @param string $locale
     * @return Response
     */
    public function compileAction(Request $request, $locale = null)
    {
        if (!$locale) {
            $locale = $request->getLocale();
        }

        /* @var Translator $translator */
        $translator = $this->get('translator');

        $tokenGroups = array();
        foreach ($translator->getCatalogue($locale)->all($this->getDomain()) as $fullToken => $translation) {
            $className = explode('.', $fullToken);
            $token = array_pop($className);
            $className = implode('.', $className);

            if (!isset($tokenGroups[$className])) {
                $tokenGroups[$className] = array();
            }

            $tokenGroups[$className][$token] = $translator->trans($fullToken, array(), $this->getDomain(), $locale);
        }

        $body = $this->renderView($this->getTemplate(), array(
            'locale' => $locale,
            'direction' => LocaleHelper::getDirection($locale),
            'token_groups' => $tokenGroups,
        ));

        return new Response($body, 200, array('Content-Type' => 'application/javascript; charset=UTF-8'));
    }
}
