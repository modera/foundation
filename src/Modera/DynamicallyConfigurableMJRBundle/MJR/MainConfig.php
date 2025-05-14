<?php

namespace Modera\DynamicallyConfigurableMJRBundle\MJR;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;
use Modera\DynamicallyConfigurableMJRBundle\Resolver\ValueResolverInterface;
use Modera\MjrIntegrationBundle\Config\MainConfigInterface;

/**
 * This implementation read configuration properties stored in central settings storage provided by
 * {@class \Modera\ConfigBundle\ModeraConfigBundle}.
 *
 * @copyright 2014 Modera Foundation
 */
class MainConfig implements MainConfigInterface
{
    public function __construct(
        private readonly ConfigurationEntriesManagerInterface $mgr,
        private readonly ?ValueResolverInterface $resolver = null,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->findAndResolve(Bundle::CONFIG_TITLE);
    }

    public function getUrl(): ?string
    {
        return $this->findAndResolve(Bundle::CONFIG_URL);
    }

    public function getHomeSection(): ?string
    {
        return $this->findAndResolve(Bundle::CONFIG_HOME_SECTION);
    }

    private function findAndResolve(string $name): ?string
    {
        $value = $this->mgr->findOneByNameOrDie($name)->getValue();
        if ($this->resolver) {
            $value = $this->resolver->resolve($name, $value);
        }

        return \is_string($value) ? $value : null;
    }
}
