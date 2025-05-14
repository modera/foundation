<?php

namespace Modera\TranslationsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Modera\LanguagesBundle\Entity\Language;

/**
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_translations_translationtoken')]
#[ORM\UniqueConstraint(name: 'domain_token_name', columns: ['domain', 'tokenName'], options: ['lengths' => [255, 767]])]
class TranslationToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $domain;

    #[ORM\Column(type: 'text', options: ['collation' => 'utf8_bin'])]
    private string $tokenName;

    /**
     * Marks that given token doesn't anymore exist in a source it has been extracted from. For example,
     * initially a token has been imported from a template, but this template has been deleted since then.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isObsolete = false;

    /**
     * @var Collection<int, LanguageTranslationToken>
     */
    #[ORM\OneToMany(targetEntity: LanguageTranslationToken::class, mappedBy: 'translationToken', cascade: ['persist', 'remove'])]
    private Collection $languageTranslationTokens;

    public function __construct()
    {
        $this->languageTranslationTokens = new ArrayCollection();
    }

    public function createLanguageToken(Language $language): LanguageTranslationToken
    {
        $languageToken = new LanguageTranslationToken();
        $languageToken->setLanguage($language);

        $this->addLanguageTranslationToken($languageToken);

        return $languageToken;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getTokenName(): string
    {
        return $this->tokenName;
    }

    public function setTokenName(string $tokenName): self
    {
        $this->tokenName = $tokenName;

        return $this;
    }

    /**
     * For ModeraServerCrudBundle.
     */
    public function getIsObsolete(): bool
    {
        return $this->isObsolete();
    }

    public function isObsolete(): bool
    {
        return $this->isObsolete;
    }

    public function setObsolete(bool $isObsolete): self
    {
        $this->isObsolete = $isObsolete;

        return $this;
    }

    public function addLanguageTranslationToken(LanguageTranslationToken $languageTranslationToken): self
    {
        if (!$this->languageTranslationTokens->contains($languageTranslationToken)) {
            $languageTranslationToken->setTranslationToken($this);
            $this->languageTranslationTokens[] = $languageTranslationToken;
        }

        return $this;
    }

    /**
     * @return Collection<int, LanguageTranslationToken>
     */
    public function getLanguageTranslationTokens(): Collection
    {
        return $this->languageTranslationTokens;
    }

    /**
     * @param Collection<int, LanguageTranslationToken> $languageTranslationTokens
     */
    public function setLanguageTranslationTokens(Collection $languageTranslationTokens): self
    {
        $this->languageTranslationTokens = $languageTranslationTokens;

        return $this;
    }
}
