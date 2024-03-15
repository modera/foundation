<?php

namespace Modera\MJRCacheAwareClassLoaderBundle\Controller;

use Modera\MJRCacheAwareClassLoaderBundle\VersionResolving\VersionResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class DefaultController extends Controller
{
    /**
     * @Route(path="%modera_mjr_cache_aware_class_loader.route%", name="modera_mjr_cache_aware_class_loader")
     */
    public function classLoaderAction(): Response
    {
        /** @var VersionResolverInterface $versionProvider */
        $versionProvider = $this->container->get('modera_mjr_cache_aware_class_loader.version_resolver');

        $content = $this->renderView('@ModeraMJRCacheAwareClassLoader/Default/class-loader.html.twig', [
            'version' => \trim($versionProvider->resolve()),
        ]);

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'application/javascript',
        ]);
    }
}
