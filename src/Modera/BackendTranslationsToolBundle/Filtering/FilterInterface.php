<?php

namespace Modera\BackendTranslationsToolBundle\Filtering;

/**
 * @copyright 2014 Modera Foundation
 */
interface FilterInterface
{
    /**
     * Technical name of filter. Used as a key in arrays/forms.
     */
    public function getId(): string;

    /**
     * Human-readable name of filter.
     */
    public function getName(): string;

    /**
     * Returns filtered data.
     *
     * @param array<mixed> $params
     *
     * @return array{
     *     'success': boolean,
     *     'items': object[],
     *     'total': int,
     * }
     */
    public function getResult(array $params): array;

    /**
     * Returns total.
     *
     * @param array<mixed> $params
     */
    public function getCount(array $params): int;

    /**
     * Checks if filter is allowed.
     */
    public function isAllowed(): bool;
}
