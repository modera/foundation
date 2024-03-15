<?php

namespace Modera\ConfigBundle\Twig;

use Modera\ConfigBundle\Manager\ConfigurationEntriesManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * You may rely on functions exposed by this class but the class itself may be moved or renamed later.
 *
 * @private
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class TwigExtension extends AbstractExtension
{
    private ConfigurationEntriesManagerInterface $configEntriesManager;

    public function __construct(ConfigurationEntriesManagerInterface $configEntriesManager)
    {
        $this->configEntriesManager = $configEntriesManager;
    }

    public function getName(): string
    {
        return 'modera_config';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('modera_config_value', [$this, 'twigModeraConfigValue']),
            new TwigFunction('modera_config_owner_value', [$this, 'getModeraConfigOwnerValue']),
        ];
    }

    /**
     * Gets values of a configuration property.
     *
     * @private
     *
     * @param string $propertyName "name" of ConfigurationEntry
     * @param bool   $strict       If FALSE is given and property is not found then no exception will be thrown
     *
     * @return mixed|null
     */
    public function twigModeraConfigValue(string $propertyName, bool $strict = true)
    {
        return $this->getModeraConfigOwnerValue($propertyName, null, $strict);
    }

    /**
     * @private
     *
     * @return mixed|null
     */
    public function getModeraConfigOwnerValue(string $propertyName, ?object $owner = null, bool $strict = true)
    {
        $mgr = $this->configEntriesManager;

        if ($strict) {
            return $mgr->findOneByNameOrDie($propertyName, $owner)->getValue();
        } else {
            $property = $mgr->findOneByName($propertyName, $owner);

            return $property ? $property->getValue() : null;
        }
    }
}
