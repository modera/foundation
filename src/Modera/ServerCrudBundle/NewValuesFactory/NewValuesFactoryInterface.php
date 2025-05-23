<?php

namespace Modera\ServerCrudBundle\NewValuesFactory;

/**
 * Implementations are responsible for formatting default values data structure that later will be used on client
 * side when creating a new record of given type. Entity class can be resolved by used $config parameter.
 *
 * @copyright 2014 Modera Foundation
 */
interface NewValuesFactoryInterface
{
    /**
     * Must return an array or instances of \stdClass ( something that can be serialized and sent back to client-side )
     * that will be used on client-side as default values for a new record.
     *
     * @param array<string, mixed> $params
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function getValues(array $params, array $config): array;
}
