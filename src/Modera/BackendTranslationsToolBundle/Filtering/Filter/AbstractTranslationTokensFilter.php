<?php

namespace Modera\BackendTranslationsToolBundle\Filtering\Filter;

use Modera\BackendTranslationsToolBundle\Filtering\FilterInterface;
use Modera\ServerCrudBundle\DependencyInjection\ModeraServerCrudExtension;
use Modera\ServerCrudBundle\Exceptions\BadConfigException;
use Modera\ServerCrudBundle\Persistence\DoctrineRegistryPersistenceHandler;
use Modera\ServerCrudBundle\Persistence\PersistenceHandlerInterface;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2014 Modera Foundation
 */
abstract class AbstractTranslationTokensFilter implements FilterInterface
{
    public function __construct(
        protected readonly ContainerInterface $container,
    ) {
    }

    protected function getPersistenceHandler(): PersistenceHandlerInterface
    {
        $config = $this->container->getParameter(ModeraServerCrudExtension::CONFIG_KEY);
        if (!\is_array($config)) {
            $config = [];
        }

        $serviceId = DoctrineRegistryPersistenceHandler::class;
        if (isset($config[$serviceType = 'persistence_handler'])) {
            $serviceId = $config[$serviceType];
        }

        try {
            /** @var PersistenceHandlerInterface $service */
            $service = $this->container->get($serviceId);

            return $service;
        } catch (\Exception $e) {
            throw BadConfigException::create($serviceType, $config, $e);
        }
    }

    public function getCount(array $params): int
    {
        if (isset($params['filter']) && !\is_array($params['filter'])) {
            $params['filter'] = [];
        }

        return $this->getPersistenceHandler()->getCount(TranslationToken::class, $params);
    }

    public function getResult(array $params): array
    {
        if (isset($params['filter']) && !\is_array($params['filter'])) {
            $params['filter'] = [];
        }

        $total = $this->getCount($params);
        $entities = [];
        if ($total > 0) {
            $entities = $this->getPersistenceHandler()->query(TranslationToken::class, $params);
        }

        return [
            'success' => true,
            'items' => $entities,
            'total' => $total,
        ];
    }

    public function isAllowed(): bool
    {
        return true;
    }
}
