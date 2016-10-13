<?php

namespace Modera\BackendLanguagesBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2016 Modera Foundation
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('_locale')) {
            $session = $request->getSession();
            if ($session && $locale = $session->get('_backend_locale')) {
                $request->attributes->set('_locale', $locale);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 90)),
        );
    }
}