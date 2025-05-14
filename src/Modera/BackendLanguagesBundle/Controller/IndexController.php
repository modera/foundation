<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Modera\LanguagesBundle\Helper\LocaleHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2020 Modera Foundation
 */
#[AsController]
class IndexController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

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
        $messages = [];

        if ($this->translator instanceof Translator) {
            foreach ($this->translator->getCatalogue($locale)->all($this->getDomain()) as $token => $translation) {
                $messages[$token] = $this->translator->trans($token, [], $this->getDomain(), $locale);
            }
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
