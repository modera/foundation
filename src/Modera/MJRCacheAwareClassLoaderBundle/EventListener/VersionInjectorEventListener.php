<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\EventListener;

use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\VersionResolverInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @internal
 *
 * For requests which match path defined in semantic configuration at
 * modera_mjr_cache_aware_class_loader/listener_response_paths this listener will add a header containing version
 * number
 *
 * @copyright 2016 Modera Foundation
 */
class VersionInjectorEventListener
{
    public const HEADER_NAME = 'X-Modera-Version';

    /**
     * @param array{'listener_response_paths': string[]} $semanticConfig
     */
    public function __construct(
        private readonly VersionResolverInterface $versionResolver,
        private readonly array $semanticConfig,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        foreach ($this->semanticConfig['listener_response_paths'] as $path) {
            if (\preg_match("@$path@", $event->getRequest()->getPathInfo())) {
                $event->getResponse()->headers->set(self::HEADER_NAME, $this->versionResolver->resolve());

                return;
            }
        }
    }
}
