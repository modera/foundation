<?php

namespace Modera\LanguagesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Locales;

/**
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_languages_language')]
#[ORM\UniqueConstraint(name: 'locale', columns: ['locale'])]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $locale = 'en';

    #[ORM\Column(type: 'boolean')]
    private bool $isEnabled = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isDefault = false;

    /**
     * Returns the name of a locale.
     */
    public static function getLocaleName(string $locale, ?string $displayLocale = null): string
    {
        $str = null;
        try {
            $str = Locales::getName($locale, $displayLocale ?: $locale);
        } catch (MissingResourceException $e) {
        }

        if (!$str) {
            $parts = \explode('_', $locale);
            if (\count($parts) > 1) {
                $code = \array_pop($parts);
                $country = $code;
                try {
                    $country = Countries::getName($code, $displayLocale ?: $parts[0]);
                } catch (MissingResourceException $e) {
                }
                while (\count($parts) && !$str) {
                    $value = \implode('_', $parts);
                    $str = null;
                    try {
                        $str = Locales::getName($value, $displayLocale ?: $value);
                    } catch (MissingResourceException $e) {
                    }
                    \array_pop($parts);
                }

                if ($str) {
                    if (')' === \substr($str, -1)) {
                        $str = \substr($str, 0, -1).', '.$country.')';
                    } else {
                        $str .= ' ('.$country.')';
                    }
                }
            }
        }

        $enc = 'utf-8';
        $name = $str ?: $locale;

        return \mb_strtoupper(\mb_substr($name, 0, 1, $enc), $enc).\mb_substr($name, 1, \mb_strlen($name, $enc), $enc);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(?string $displayLocale = null): string
    {
        return static::getLocaleName($this->getLocale(), $displayLocale);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale ?: 'en';
    }

    /**
     * For ModeraServerCrudBundle.
     */
    public function getIsEnabled(): bool
    {
        return $this->isEnabled();
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setEnabled(bool $status): void
    {
        $this->isEnabled = $status;
    }

    /**
     * For ModeraServerCrudBundle.
     */
    public function getIsDefault(): bool
    {
        return $this->isDefault();
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setDefault(bool $status): void
    {
        $this->isDefault = $status;
    }
}
