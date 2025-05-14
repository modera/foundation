<?php

namespace Modera\ServerCrudBundle\Service;

use Modera\ServerCrudBundle\DependencyInjection\ModeraServerCrudExtension;
use Modera\ServerCrudBundle\Exceptions\BadConfigException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 *
 * @copyright 2025 Modera Foundation
 */
class ConfiguredServiceManager
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function get(string $serviceType): object
    {
        /** @var array<string, string> $config */
        $config = $this->container->getParameter(ModeraServerCrudExtension::CONFIG_KEY);

        try {
            /** @var object $service */
            $service = $this->container->get(isset($config[$serviceType]) ? $config[$serviceType] : '');

            return $service;
        } catch (\Exception $e) {
            throw BadConfigException::create($serviceType, $config, $e);
        }
    }
}
