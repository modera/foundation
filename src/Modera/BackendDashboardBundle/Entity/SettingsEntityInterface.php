<?php

namespace Modera\BackendDashboardBundle\Entity;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
interface SettingsEntityInterface
{
    public function getId(): ?int;

    /**
     * @param array<string, mixed> $settings
     */
    public function setDashboardSettings(array $settings): void;

    /**
     * @return array<string, mixed>
     */
    public function getDashboardSettings(): array;

    public function describeEntity(): string;
}
