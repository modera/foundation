<?php

namespace Modera\ConfigBundle\Config;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * This exception will be thrown when some required configuration parameters for
 * {@class EntityRepositoryHandler} are not provided.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class MissingConfigurationParameterException extends \RuntimeException
{
    private ?string $parameter = null;

    public function setParameter(string $parameter): void
    {
        $this->parameter = $parameter;
    }

    public function getParameter(): ?string
    {
        return $this->parameter;
    }

    public static function create(ConfigurationEntry $entry, string $parameter): self
    {
        $me = new self(\sprintf(
            '%s::getServerHandlerConfig(): configuration property "%s" for ConfigurationEntry with id "%s" is not provided!',
            \get_class($entry),
            $parameter,
            $entry->getId()
        ));
        $me->setParameter($parameter);

        return $me;
    }
}
