<?php

namespace Modera\TranslationsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Modera\LanguagesBundle\Entity\Language;

/**
 * If you change a table name then don't forget to update
 * \Modera\ModuleBundle\Composer\ScriptHandler::updateTranslationsTableCollation.
 *
 * @ORM\Entity
 * @ORM\Table(name="modera_translations_translationtoken", options={"collate"="utf8_bin"})
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class TranslationToken
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $bundleName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $domain;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $tokenName;

    /**
     * Marks that given token doesn't anymore exists in a source it has been extracted from. For example,
     * initially a token has been imported from a template, but this template has been deleted since then.
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isObsolete = false;

    /**
     * See {@class \Modera\TranslationsBundle\EventListener\LanguageTranslationTokenListener} for details.
     *
     * @var array
     * @ORM\Column(type="json_array", nullable=false)
     */
    private $translations = array();

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="LanguageTranslationToken", mappedBy="translationToken", cascade={"persist", "remove"})
     */
    private $languageTranslationTokens;

    public function __construct()
    {
        $this->languageTranslationTokens = new ArrayCollection();
    }

    /**
     * @since 2.55.0
     *
     * @param Language $language
     *
     * @return LanguageTranslationToken
     */
    public function createLanguageToken(Language $language)
    {
        $languageToken = new LanguageTranslationToken();
        $languageToken->setLanguage($language);

        $this->addLanguageTranslationToken($languageToken);

        return $languageToken;
    }

    public static function clazz()
    {
        return get_called_class();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * @param string $bundleName
     *
     * @return TranslationToken
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return TranslationToken
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

    /**
     * @param string $tokenName
     *
     * @return TranslationToken
     */
    public function setTokenName($tokenName)
    {
        $this->tokenName = $tokenName;

        return $this;
    }

    /**
     * For ModeraServerCrudBundle.
     *
     * @return bool
     */
    public function getIsObsolete()
    {
        return $this->isObsolete;
    }

    /**
     * @return bool
     */
    public function isObsolete()
    {
        return $this->isObsolete;
    }

    /**
     * @param bool $isObsolete
     *
     * @return TranslationToken
     */
    public function setObsolete($isObsolete)
    {
        $this->isObsolete = $isObsolete;

        return $this;
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param array $translations
     *
     * @return TranslationToken
     */
    public function setTranslations(array $translations)
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * @param LanguageTranslationToken $languageTranslationToken
     *
     * @return TranslationToken
     */
    public function addLanguageTranslationToken(LanguageTranslationToken $languageTranslationToken)
    {
        if (!$this->languageTranslationTokens->contains($languageTranslationToken)) {
            $languageTranslationToken->setTranslationToken($this);
            $this->languageTranslationTokens[] = $languageTranslationToken;
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLanguageTranslationTokens()
    {
        return $this->languageTranslationTokens;
    }

    /**
     * @param ArrayCollection $languageTranslationTokens
     *
     * @return TranslationToken
     */
    public function setLanguageTranslationTokens(ArrayCollection $languageTranslationTokens)
    {
        $this->languageTranslationTokens = $languageTranslationTokens;

        return $this;
    }
}
