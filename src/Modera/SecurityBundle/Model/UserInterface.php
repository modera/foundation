<?php

namespace Modera\SecurityBundle\Model;

/**
 * @copyright 2014 Modera Foundation
 */
interface UserInterface
{
    public const GENDER_MALE = 'm';
    public const GENDER_FEMALE = 'f';

    public const STATE_NEW = 0;
    public const STATE_ACTIVE = 1;

    public function getEmail(): ?string;

    public function getUsername(): ?string;

    public function getPersonalId(): ?string;

    public function getFirstName(): ?string;

    public function getLastName(): ?string;

    public function getMiddleName(): ?string;

    public function getFullName(string $pattern = 'first last'): ?string;

    public function getGender(): ?string;

    public function getState(): int;

    public function getLastLogin(): ?\DateTimeInterface;

    public function isActive(): bool;
}
