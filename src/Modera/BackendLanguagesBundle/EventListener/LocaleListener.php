<?php

namespace Modera\BackendLanguagesBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2016 Modera Foundation
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var FirewallMap
     */
    private $firewallMap;

    /**
     * @param FirewallMap $firewallMap
     */
    public function __construct(FirewallMap $firewallMap)
    {
        $this->firewallMap = $firewallMap;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $firewall = $this->firewallMap->getFirewallConfig($request);

        if (!$request->attributes->get('_locale') && $firewall->isSecurityEnabled()) {
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
