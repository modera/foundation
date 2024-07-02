<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Modera\LanguagesBundle\Helper\LocaleHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
class IndexController extends Controller
{
    protected function getDomain(): string
    {
        return 'extjs';
    }

    protected function getTemplate(): string
    {
        return '@ModeraBackendLanguages/Index/compile.js.twig';
    }

    /**
     * @return array<string, string>
     */
    protected function getTranslations(string $locale): array
    {
        /** @var Translator $translator */
        $translator = $this->container->get('translator');

        $messages = [];
        foreach ($translator->getCatalogue($locale)->all($this->getDomain()) as $token => $translation) {
            $messages[$token] = $translator->trans($token, [], $this->getDomain(), $locale);
        }

        return $messages;
    }

    public function compileAction(Request $request, ?string $locale = null): Response
    {
        if (!$locale) {
            $locale = $request->getLocale();
        }

        $tokenGroups = [];
        foreach ($this->getTranslations($locale) as $fullToken => $translation) {
            $className = \explode('.', $fullToken);
            $token = \array_pop($className);
            $className = \implode('.', $className);

            if (!isset($tokenGroups[$className])) {
                $tokenGroups[$className] = [];
            }

            $tokenGroups[$className][$token] = $translation;
        }

        $body = $this->renderView($this->getTemplate(), [
            'locale' => $locale,
            'direction' => LocaleHelper::getDirection($locale),
            'token_groups' => $tokenGroups,
        ]);

        return new Response($body, 200, ['Content-Type' => 'application/javascript; charset=UTF-8']);
    }
}
