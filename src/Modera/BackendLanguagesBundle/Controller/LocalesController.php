<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\LanguagesBundle\Entity\Language;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @copyright 2018 Modera Foundation
 */
#[AsController]
class LocalesController extends AbstractController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ExtensionProvider $extensionProvider,
        private readonly RequestStack $requestStack,
    ) {
    }

    protected function checkAccess(): void
    {
        $role = ModeraMJRSecurityIntegrationBundle::ROLE_BACKEND_USER;
        if (false === $this->authorizationChecker->isGranted($role)) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @Remote
     *
     * @param array<mixed> $params
     *
     * @return array<mixed>
     */
    public function listAction(array $params): array
    {
        $this->checkAccess();

        $locales = \array_filter($this->getLocales(), function ($locale) use ($params) {
            $parts = \explode('_', $locale);
            if (\count($parts) > 1) {
                if (isset($params['ignore']) && \is_array($params['ignore']) && \in_array($locale, $params['ignore'])) {
                    return false;
                }

                return !isset($params['language']) || $params['language'] === $parts[0];
            }

            return false;
        });

        $arr = [];
        foreach ($locales as $locale) {
            $value = Language::getLocaleName($locale, $this->getDisplayLocale());
            if (isset($params['language'])) {
                $value = \substr(\explode('(', $value, 2)[1], 0, -1);
            }
            $arr[$locale] = $value;
        }

        $collator = new \Collator($this->getDisplayLocale());
        $collator->asort($arr);

        $result = [];
        foreach ($arr as $locale => $name) {
            $result[] = [
                'id' => $locale,
                'name' => $name,
            ];
        }

        return [
            'success' => true,
            'items' => $result,
            'total' => \count($result),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function getLocales(): array
    {
        $locales = \array_keys(Locales::getNames());

        $id = 'modera_backend_languages.locales';
        if ($this->extensionProvider->has($id)) {
            /** @var string $locale */
            foreach ($this->extensionProvider->get($id)->getItems() as $locale) {
                $locales[] = $locale;
            }
        }

        return $locales;
    }

    private function getDisplayLocale(): string
    {
        return $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';
    }
}
