<?php

namespace Modera\ConfigBundle\Config;

use Modera\ConfigBundle\Entity\ConfigurationEntry;

/**
 * @copyright 2014 Modera Foundation
 */
interface HandlerInterface
{
    /**
     * Value that will be displayed in the frontend (list view).
     */
    public function getReadableValue(ConfigurationEntry $entry): mixed;

    /**
     * Oftentimes value stored in {@class ConfigurationEntry} will be some entity
     * primary key and your handler will use it to return an entity.
     */
    public function getValue(ConfigurationEntry $entry): mixed;

    /**
     * Takes a value (it can be an object or whatever) that came from client side(or from some other place) and converts
     * it to something that can be saved in database.
     */
    public function convertToStorageValue(mixed $value, ConfigurationEntry $entry): mixed;
}
