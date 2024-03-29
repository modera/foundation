<?php

namespace Modera\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\Firewall\SwitchUserListener;
use Modera\SecurityBundle\ModeraSecurityBundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class SwitchUserSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var string
     */
    private $usernameParameter;

    /**
     * @param array $bundleConfig
     */
    public function __construct(array $bundleConfig = array()) {
        if (isset($bundleConfig['switch_user']) && $bundleConfig['switch_user']) {
            $this->usernameParameter = $bundleConfig['switch_user']['parameter'];
        }
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if ($this->redirectUri) {
            $event->setResponse(new RedirectResponse($this->redirectUri));
            $this->redirectUri = null;
        }
    }

    /**
     * @param SwitchUserEvent $event
     */
    public function onSwitchUser(SwitchUserEvent $event)
    {
        if ($this->usernameParameter) {
            $request    = $event->getRequest();
            $targetUser = $event->getTargetUser();

            $username = $request->get($this->usernameParameter) ?: $request->headers->get($this->usernameParameter);

            if (SwitchUserListener::EXIT_VALUE !== $username) {
                $exit = false;
                $token = $event->getToken();
                if ($token instanceof SwitchUserToken) {
                    $exit = $token->getOriginalToken()->getUser()->getUsername() === $targetUser->getUsername();
                }

                if ($exit || in_array(ModeraSecurityBundle::ROLE_ROOT_USER, $targetUser->getRoles())) {
                    $this->redirectUri = str_replace($targetUser->getUsername(), SwitchUserListener::EXIT_VALUE, $request->getUri());
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }
}