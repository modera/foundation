<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Controller;

use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\VersionResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsController]
class DefaultController extends AbstractController
{
    public function __construct(
        private readonly VersionResolverInterface $versionResolver,
    ) {
    }

    #[Route(path: '%modera_mjr_cache_aware_class_loader.route%', name: 'modera_mjr_cache_aware_class_loader')]
    public function classLoaderAction(): Response
    {
        $content = $this->renderView('@ModeraMJRCacheAwareClassLoader/Default/class-loader.html.twig', [
            'version' => \trim($this->versionResolver->resolve()),
        ]);

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'application/javascript',
        ]);
    }
}
