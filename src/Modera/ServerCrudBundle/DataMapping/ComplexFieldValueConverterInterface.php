<?php

namespace Modera\ServerCrudBundle\DataMapping;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @copyright 2024 Modera Foundation
 */
interface ComplexFieldValueConverterInterface
{
    public function isResponsible(mixed $value, string $fieldName, ClassMetadataInfo $meta): bool;

    public function convert(mixed $value, string $fieldName, ClassMetadataInfo $meta): mixed;
}
