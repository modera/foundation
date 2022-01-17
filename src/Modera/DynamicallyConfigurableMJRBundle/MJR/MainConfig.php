<?php

namespace Modera\DynamicallyConfigurableMJRBundle\MJR;

use Modera\MjrIntegrationBundle\Config\MainConfigInterface;
use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;

/**
 * This implementation read configuration properties stored in central settings storage provided by
 * {@class \Modera\ConfigBundle\ModeraConfigBundle}.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class MainConfig implements MainConfigInterface
{
    /**
     * @var ConfigurationEntriesManagerInterface
     */
    private $mgr;

    /**
     * @var ValueResolverInterface|null
     */
    private $resolver;

    /**
     * @param ConfigurationEntriesManagerInterface $mgr
     * @param ValueResolverInterface|null $resolver
     */
    public function __construct(ConfigurationEntriesManagerInterface $mgr, ValueResolverInterface $resolver = null)
    {
        $this->mgr = $mgr;
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->findAndResolve(Bundle::CONFIG_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->findAndResolve(Bundle::CONFIG_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function getHomeSection()
    {
        return $this->findAndResolve(Bundle::CONFIG_HOME_SECTION);
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function findAndResolve($name)
    {
        $value = $this->mgr->findOneByNameOrDie($name)->getValue();
        if ($this->resolver) {
            $value = $this->resolver->resolve($name, $value);
        }
        return $value;
    }
}
