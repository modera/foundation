<?php

namespace Modera\ServerCrudBundle\QueryBuilder\Parsing;

/**
 * @copyright 2024 Modera Foundation
 */
interface FilterInterface
{
    public function isValid(): bool;

    /**
     * @return array{
     *     'property': string,
     *     'value': string,
     * }|array{
     *     'property': string,
     *     'value': string,
     * }[]
     */
    public function compile(): array;
}
