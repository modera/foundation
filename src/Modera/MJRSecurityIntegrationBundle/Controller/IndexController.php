<?php

namespace Modera\MJRSecurityIntegrationBundle\Controller;

use Modera\ExpanderBundle\Ext\ExtensionProvider;
use Modera\MjrIntegrationBundle\AssetsHandling\AssetsProviderInterface;
use Modera\MjrIntegrationBundle\ClientSideDependencyInjection\ServiceDefinitionsManager;
use Modera\MjrIntegrationBundle\Config\MainConfigInterface;
use Modera\MjrIntegrationBundle\DependencyInjection\ModeraMjrIntegrationExtension;
use Modera\MJRSecurityIntegrationBundle\DependencyInjection\ModeraMJRSecurityIntegrationExtension;
use Modera\MJRSecurityIntegrationBundle\ModeraMJRSecurityIntegrationBundle;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Modera\SecurityBundle\Security\Authenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Entry point to web application.
 *
 * @copyright 2013 Modera Foundation
 */
#[AsController]
class IndexController extends AbstractController
{
    public function __construct(
        private readonly AssetsProviderInterface $assetsProvider,
        private readonly ExtensionProvider $extensionProvider,
        private readonly MainConfigInterface $mainConfig,
        private readonly ServiceDefinitionsManager $serviceDefinitionsManager,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * Entry point MF backend.
     */
    #[Route(path: '/')]
    public function indexAction(): Response
    {
        /** @var array<string, mixed> $runtimeConfig */
        $runtimeConfig = $this->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY);

        /** @var array<string, mixed> $securedRuntimeConfig */
        $securedRuntimeConfig = $this->getParameter(ModeraMJRSecurityIntegrationExtension::CONFIG_KEY);

        $classLoaderMappingsProvider = $this->extensionProvider->get('modera_mjr_integration.bootstrapping_class_loader_mappings');

        $runtimeConfig['home_section'] = $this->mainConfig->getHomeSection();
        $runtimeConfig['deployment_name'] = $this->mainConfig->getTitle();
        $runtimeConfig['deployment_url'] = $this->mainConfig->getUrl();
        $runtimeConfig['class_loader_mappings'] = $classLoaderMappingsProvider->getItems();

        // converting URL like /backend/ModeraFoundation/Application.js to /backend/ModeraFoundation
        $appLoadingPath = $this->generateUrl('modera_mjr_security_integration.index.application');
        $appLoadingPath = \substr($appLoadingPath, 0, \strpos($appLoadingPath, 'Application.js') - 1);

        $content = $this->renderView(
            '@ModeraMJRSecurityIntegration/Index/index.html.twig',
            [
                'config' => \array_merge($runtimeConfig, $securedRuntimeConfig),
                'css_resources' => $this->assetsProvider->getCssAssets(AssetsProviderInterface::TYPE_BLOCKING),
                'js_resources' => $this->assetsProvider->getJavascriptAssets(AssetsProviderInterface::TYPE_BLOCKING),
                'app_loading_path' => $appLoadingPath,
                'disable_caching' => 'prod' !== $this->getParameter('kernel.environment'),
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
     * @see Resources/config/routing.yaml
     * @see \Modera\MJRSecurityIntegrationBundle\Contributions\RoutingResourcesProvider
     */
    public function applicationAction(): Response
    {
        $nonBlockingResources = [
            'css' => $this->assetsProvider->getCssAssets(AssetsProviderInterface::TYPE_NON_BLOCKING),
            'js' => $this->assetsProvider->getJavascriptAssets(AssetsProviderInterface::TYPE_NON_BLOCKING),
        ];

        $content = $this->renderView(
            '@ModeraMJRSecurityIntegration/Index/application.html.twig',
            [
                'non_blocking_resources' => $nonBlockingResources,
                'container_services' => $this->serviceDefinitionsManager->getDefinitions(),
                'config' => $this->getParameter(ModeraMjrIntegrationExtension::CONFIG_KEY),
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

        $token = $this->tokenStorage->getToken();

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

    public function switchUserToAction(string $username): RedirectResponse
    {
        $url = '/';

        /** @var ?array{'parameter': string} $switchUserConfig */
        $switchUserConfig = $this->getParameter(ModeraSecurityExtension::CONFIG_KEY.'.switch_user');
        if ($switchUserConfig) {
            $parameters = [];
            $parameters[$switchUserConfig['parameter']] = $username;
            /** @var string $isAuthenticatedRoute */
            $isAuthenticatedRoute = $this->getParameter(
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
