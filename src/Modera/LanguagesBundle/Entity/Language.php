<?php

namespace Modera\LanguagesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Locales;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="modera_languages_language", uniqueConstraints={
 *
 *     @ORM\UniqueConstraint(name="locale", columns={"locale"})
 * })
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class Language
{
    /**
     * @ORM\Column(type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $locale = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isEnabled = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isDefault = false;

    /**
     * @deprecated Use native ::class property
     */
    public static function clazz(): string
    {
        @\trigger_error(\sprintf(
            'The "%s()" method is deprecated. Use native ::class property.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return \get_called_class();
    }

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
        return $this->locale ?: 'en';
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
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

    /**
     * @deprecated Since v3.1.0, use Language::isEnabled() method.
     */
    public function getEnabled(): bool
    {
        @\trigger_error(sprintf(
            'The "%s()" method is deprecated. Use Language::isEnabled() method.',
            __METHOD__
        ), \E_USER_DEPRECATED);

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
