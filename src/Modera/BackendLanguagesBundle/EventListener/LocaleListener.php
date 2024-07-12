<?php

namespace Modera\BackendLanguagesBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Modera\BackendLanguagesBundle\Entity\UserSettings;
use Modera\LanguagesBundle\Entity\Language;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2016 Modera Foundation
 */
class LocaleListener implements EventSubscriberInterface
{
    private const LOCALE = 'LOCALE';

    private EntityManagerInterface $em;

    private string $defaultLocale;

    private string $isAuthenticatedRoute;

    public function __construct(
        EntityManagerInterface $em,
        string $defaultLocale = 'en',
        string $isAuthenticatedRoute = 'modera_mjr_security_integration.index.is_authenticated'
    ) {
        $this->em = $em;
        $this->defaultLocale = $defaultLocale;
        $this->isAuthenticatedRoute = $isAuthenticatedRoute;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($event->isMainRequest() && !$request->attributes->get('_locale')) {
            if ($locale = $request->cookies->get(self::LOCALE)) {
                $request->attributes->set('_locale', $locale);
            }
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ('modera_mjrsecurityintegration_index_index' === $request->attributes->get('_route')) {
            $response->headers->set('set-cookie', self::LOCALE);
        }

        if ($this->isAuthenticatedRoute === $request->attributes->get('_route')) {
            if ($response instanceof JsonResponse) {
                $content = \json_decode($response->getContent() ?: '', true);
                if (\is_array($content) && $content['success']) {
                    $locale = null;

                    /** @var ?UserSettings $settings */
                    $settings = $this->em->getRepository(UserSettings::class)->findOneBy([
                        'user' => $content['profile']['id'],
                    ]);
                    if ($settings && $settings->getLanguage() && $settings->getLanguage()->isEnabled()) {
                        $locale = $settings->getLanguage()->getLocale();
                    }

                    if (!$locale) {
                        /** @var ?Language $defaultLanguage */
                        $defaultLanguage = $this->em->getRepository(Language::class)->findOneBy([
                            'isDefault' => true,
                        ]);
                        if ($defaultLanguage) {
                            $locale = $defaultLanguage->getLocale();
                        }
                    }

                    $response->headers->set('set-cookie', self::LOCALE.'='.($locale ?: $this->defaultLocale));
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 90]],
            KernelEvents::RESPONSE => [['onKernelResponse', 90]],
        ];
    }
}
