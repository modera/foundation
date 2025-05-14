<?php

namespace Modera\ServerCrudBundle\QueryBuilder\ResolvingAssociatedModelSortingField;

/**
 * @copyright 2024 Modera Foundation
 *
 * The attribute can be placed on a root class ( the class that will be used in association ) or a property
 * where the association is mapped in a related entity. Attribute defines which field-name from the associated
 * entity must be used to order by it. For example, if we have entities Foo and Bar which related as one-to-many
 * relation using field 'Foo.bar', then if we do ORDER BY 'Foo.bar' we need to know which field we want to use
 * to order by related 'bar' entity.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
final class QueryOrder
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
