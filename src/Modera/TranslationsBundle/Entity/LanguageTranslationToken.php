<?php

namespace Modera\TranslationsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Modera\LanguagesBundle\Entity\Language;

/**
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_translations_languagetranslationtoken')]
#[ORM\UniqueConstraint(name: 'language_translation_token', columns: ['language_id', 'translation_token_id'])]
class LanguageTranslationToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Language::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'language_id', referencedColumnName: 'id')]
    private ?Language $language = null;

    #[ORM\ManyToOne(targetEntity: TranslationToken::class, inversedBy: 'languageTranslationTokens')]
    #[ORM\JoinColumn(name: 'translation_token_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TranslationToken $translationToken = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isNew = true;

    #[ORM\Column(type: 'text')]
    private string $translation;

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

    public function getTranslation(): string
    {
        return $this->translation;
    }

    public function setTranslation(string $translation): self
    {
        $this->translation = $translation;

        return $this;
    }
}
