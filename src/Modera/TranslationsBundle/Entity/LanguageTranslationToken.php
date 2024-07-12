<?php

namespace Modera\TranslationsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Modera\LanguagesBundle\Entity\Language;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="modera_translations_languagetranslationtoken", uniqueConstraints={
 *
 *     @UniqueConstraint(name="language_translation_token", columns={"language_id", "translation_token_id"})
 * })
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class LanguageTranslationToken
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
     * @ORM\ManyToOne(targetEntity="Modera\LanguagesBundle\Entity\Language", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id")
     */
    private ?Language $language = null;

    /**
     * @ORM\ManyToOne(targetEntity="TranslationToken", inversedBy="languageTranslationTokens")
     *
     * @ORM\JoinColumn(name="translation_token_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?TranslationToken $translationToken = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isNew = true;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private ?string $translation = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getTranslationToken(): ?TranslationToken
    {
        return $this->translationToken;
    }

    public function setTranslationToken(TranslationToken $translationToken): self
    {
        $this->translationToken = $translationToken;

        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setNew(bool $isNew): self
    {
        $this->isNew = $isNew;

        return $this;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setTranslation(string $translation): self
    {
        $this->translation = $translation;

        return $this;
    }
}
