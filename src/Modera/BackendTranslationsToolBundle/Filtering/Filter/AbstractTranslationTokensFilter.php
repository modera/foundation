<?php

namespace Modera\BackendTranslationsToolBundle\Filtering\Filter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Modera\ServerCrudBundle\DependencyInjection\ModeraServerCrudExtension;
use Modera\ServerCrudBundle\Exceptions\BadConfigException;
use Modera\ServerCrudBundle\Persistence\PersistenceHandlerInterface;
use Modera\BackendTranslationsToolBundle\Filtering\FilterInterface;
use Modera\TranslationsBundle\Entity\TranslationToken;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
abstract class AbstractTranslationTokensFilter implements FilterInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return PersistenceHandlerInterface
     */
    protected function getPersistenceHandler()
    {
        $config = $this->container->getParameter(ModeraServerCrudExtension::CONFIG_KEY);

        $serviceId = 'modera_server_crud.persistence.doctrine_registry_handler';
        if (isset($config[$serviceType = 'persistence_handler'])) {
            $serviceId = $config[$serviceType];
        }

        try {
            return $this->container->get($serviceId);
        } catch (\Exception $e) {
            throw BadConfigException::create($serviceType, $config, $e);
        }
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function em()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(array $params)
    {
        if (isset($params['filter']) && !is_array($params['filter'])) {
            $params['filter'] = array();
        }

        return $this->getPersistenceHandler()->getCount(TranslationToken::class, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getResult(array $params)
    {
        if (isset($params['filter']) && !is_array($params['filter'])) {
            $params['filter'] = array();
        }

        $total = $this->getCount($params);
        $entities = array();
        if ($total > 0) {
            $entities = $this->getPersistenceHandler()->query(TranslationToken::class, $params);
        }

        return array(
            'success' => true,
            'items' => $entities,
            'total' => $total,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return true;
    }
}
