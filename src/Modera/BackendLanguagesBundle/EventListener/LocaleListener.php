<?php

namespace Modera\BackendLanguagesBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Modera\BackendLanguagesBundle\Entity\UserSettings;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2016 Modera Foundation
 */
class LocaleListener implements EventSubscriberInterface
{
    const LOCALE = 'LOCALE';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($event->isMasterRequest() && !$request->attributes->get('_locale')) {
            if ($locale = $request->cookies->get(self::LOCALE)) {
                $request->attributes->set('_locale', $locale);
            }
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ('modera_mjrsecurityintegration_index_index' === $request->attributes->get('_route')) {
            $response->headers->set('set-cookie', self::LOCALE);
        }

        if ('modera_mjr_security_integration.index.is_authenticated' === $request->attributes->get('_route')) {
            if ($response instanceof JsonResponse) {
                $content = json_decode($response->getContent(), true);
                if ($content['success']) {
                    $locale = null; //
                    /* @var UserSettings $settings */
                    $settings = $this->em->getRepository(UserSettings::clazz())
                        ->findOneBy(array('user' => $content['profile']['id']));
                    if ($settings && $settings->getLanguage() && $settings->getLanguage()->getEnabled()) {
                        $locale = $settings->getLanguage()->getLocale();
                    }
                    $response->headers->set('set-cookie', self::LOCALE . '=' . $locale);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 90)),
            KernelEvents::RESPONSE => array(array('onKernelResponse', 90)),
        );
    }
}
