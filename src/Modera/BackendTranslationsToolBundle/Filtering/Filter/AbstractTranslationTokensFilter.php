<?php

namespace Modera\BackendTranslationsToolBundle\Filtering\Filter;

use Modera\BackendTranslationsToolBundle\Filtering\FilterInterface;
use Modera\ServerCrudBundle\DependencyInjection\ModeraServerCrudExtension;
use Modera\ServerCrudBundle\Exceptions\BadConfigException;
use Modera\ServerCrudBundle\Persistence\PersistenceHandlerInterface;
use Modera\TranslationsBundle\Entity\TranslationToken;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
abstract class AbstractTranslationTokensFilter implements FilterInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getPersistenceHandler(): PersistenceHandlerInterface
    {
        $config = $this->container->getParameter(ModeraServerCrudExtension::CONFIG_KEY);
        if (!\is_array($config)) {
            $config = [];
        }

        $serviceId = 'modera_server_crud.persistence.doctrine_registry_handler';
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
