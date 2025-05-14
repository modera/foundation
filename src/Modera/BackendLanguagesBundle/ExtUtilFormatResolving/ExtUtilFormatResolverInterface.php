<?php

namespace Modera\BackendLanguagesBundle\ExtUtilFormatResolving;

/**
 * @copyright 2020 Modera Foundation
 */
interface ExtUtilFormatResolverInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function resolveExtUtilFormat(string $locale, array $config = []): array;
}
