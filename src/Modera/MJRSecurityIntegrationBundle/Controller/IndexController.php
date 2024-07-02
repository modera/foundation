<?php

namespace Modera\MJRSecurityIntegrationBundle\Controller;

use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\AssetsHandling\AssetsProviderInterface;
use Modera\MjrIntegrationBundle\ClientSideDependencyInjection\ServiceDefinitionsManager;
use Modera\MjrIntegrationBundle\Config\MainConfigInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Modera\MJRSecurityIntegrationBundle\DependencyInjection\ModeraMJRSecurityIntegrationExtension;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Modera\SecurityBundle\Security\Authenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Entry point to web application.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class IndexController extends Controller
{
    protected function getContainer(): ContainerInterface
    {
        /** @var ContainerInterface $container */
        $container = $this->container;

        return $container;
    }

    /**
     * Entry point MF backend.
     *
     * @Route("/")
     */
    public function indexAction(): Response
    {
        /** @var array<string, mixed> $runtimeConfig */
        $runtimeConfig = $this->getContainer()->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);

        /** @var array<string, mixed> $securedRuntimeConfig */
        $securedRuntimeConfig = $this->getContainer()->getParameter(ModeraMJRSecurityIntegrationExtension::CONFIG_KEY);

        /** @var ContributorInterface $classLoaderMappingsProvider */
        $classLoaderMappingsProvider = $this->getContainer()->get('modera_mjr_integration.bootstrapping_class_loader_mappings_provider');

        /** @var string $mainConfigProvider */
        $mainConfigProvider = $runtimeConfig['main_config_provider'];

        /** @var MainConfigInterface $mainConfig */
        $mainConfig = $this->getContainer()->get($mainConfigProvider);
        $runtimeConfig['home_section'] = $mainConfig->getHomeSection();
        $runtimeConfig['deployment_name'] = $mainConfig->getTitle();
        $runtimeConfig['deployment_url'] = $mainConfig->getUrl();
        $runtimeConfig['class_loader_mappings'] = $classLoaderMappingsProvider->getItems();

        // for docs regarding how to use "non-blocking" assets see
        // \Modera\MjrIntegrationBundle\AssetsHandling\AssetsProvider class

        /** @var AssetsProviderInterface $assetsProvider */
        $assetsProvider = $this->getContainer()->get('modera_mjr_integration.assets_handling.assets_provider');

        /** @var RouterInterface $router */
        $router = $this->getContainer()->get('router');
        // converting URL like /app_dev.php/backend/ModeraFoundation/Application.js to /app_dev.php/backend/ModeraFoundation
        $appLoadingPath = $router->generate('modera_mjr_security_integration.index.application');
        $appLoadingPath = \substr($appLoadingPath, 0, \strpos($appLoadingPath, 'Application.js') - 1);

        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');

        $content = $this->renderView(
            '@ModeraMJRSecurityIntegration/Index/index.html.twig',
            [
                'config' => \array_merge($runtimeConfig, $securedRuntimeConfig),
                'css_resources' => $assetsProvider->getCssAssets(AssetsProviderInterface::TYPE_BLOCKING),
                'js_resources' => $assetsProvider->getJavascriptAssets(AssetsProviderInterface::TYPE_BLOCKING),
                'app_loading_path' => $appLoadingPath,
                'disable_caching' => 'prod' !== $kernel->getEnvironment(),
            ]
        );

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    /**
     * Dynamically generates an entry point to backend application, action's output is JavaScript class
     * which is used by ExtJs to bootstrap application.
     *
     * @see Resources/config/routing.yml
     * @see \Modera\MJRSecurityIntegrationBundle\Contributions\RoutingResourcesProvider
     */
    public function applicationAction(): Response
    {
        /** @var AssetsProviderInterface $assetsProvider */
        $assetsProvider = $this->getContainer()->get('modera_mjr_integration.assets_handling.assets_provider');

        $nonBlockingResources = [
            'css' => $assetsProvider->getCssAssets(AssetsProviderInterface::TYPE_NON_BLOCKING),
            'js' => $assetsProvider->getJavascriptAssets(AssetsProviderInterface::TYPE_NON_BLOCKING),
        ];

        /** @var ServiceDefinitionsManager $definitionsMgr */
        $definitionsMgr = $this->getContainer()->get('modera_mjr_integration.csdi.service_definitions_manager');
        $content = $this->renderView(
            '@ModeraMJRSecurityIntegration/Index/application.html.twig',
            [
                'non_blocking_resources' => $nonBlockingResources,
                'container_services' => $definitionsMgr->getDefinitions(),
                'config' => $this->getContainer()->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY),
            ]
        );

        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }

    /**
     * Endpoint can be used by MJR to figure out if user is already authenticated and therefore
     * runtime UI can be loaded.
     */
    public function isAuthenticatedAction(Request $request): JsonResponse
    {
        $this->initSession($request);

        /** @var TokenStorageInterface $sc */
        $sc = $this->getContainer()->get('security.token_storage');
        $token = $sc->getToken();

        $response = Authenticator::getAuthenticationResponse($token);

        if ($response['success']) {
            if (!$token || !$this->isGranted(ModeraMJRSecurityIntegrationBundle::ROLE_BACKEND_USER, $token->getUser())) {
                $response = [
                    'success' => false,
                    'message' => "You don't have required rights to access administration interface.",
                ];
            }
        }

        return new JsonResponse($response);
    }

    public function switchUserToAction(Request $request, string $username): RedirectResponse
    {
        $url = '/';

        /** @var ?array{'parameter': string} $switchUserConfig */
        $switchUserConfig = $this->getContainer()->getParameter(ModeraSecurityExtension::CONFIG_KEY.'.switch_user');
        if ($switchUserConfig) {
            $parameters = [];
            $parameters[$switchUserConfig['parameter']] = $username;
            /** @var string $isAuthenticatedRoute */
            $isAuthenticatedRoute = $this->getContainer()->getParameter(
                ModeraMJRSecurityIntegrationExtension::CONFIG_KEY.'.is_authenticated_url'
            );
            $url = $this->generateUrl($isAuthenticatedRoute, $parameters);
        }

        return $this->redirect($url);
    }

    private function initSession(Request $request): void
    {
        $session = $request->getSession();
        if ($session instanceof Session && !$session->getId()) {
            $session->start();
        }
    }
}
