<?php

namespace Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField;

/**
 * @copyright 2024 Modera Foundation
 */
interface SortingFieldResolverInterface
{
    public function resolve(string $entityFqcn, string $fieldName): ?string;
}
