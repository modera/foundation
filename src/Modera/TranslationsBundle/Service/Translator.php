<?php

namespace Modera\TranslationsBundle\Service;

use Symfony\Component\Translation\Translator as BaseTranslator;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as DefaultTranslator;
use Modera\TranslationsBundle\Compiler\Adapter\AdapterInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class Translator extends BaseTranslator implements WarmableInterface
{
    const CACHE_KEY = 'modera_translations.catalogue';

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var DefaultTranslator
     */
    private $translator;

    /**
     * @param AdapterInterface $adapter
     * @param MessageFormatterInterface $formatter
     * @param DefaultTranslator $translator
     * @param bool $debug
     */
    public function __construct(AdapterInterface $adapter, $formatter, DefaultTranslator $translator, $debug = false)
    {
        parent::__construct($translator->getLocale(), $formatter, null, $debug);

        $this->setFallbackLocales($translator->getFallbackLocales());

        $this->adapter = $adapter;
        $this->translator = $translator;
    }

    /**
     * @param string $locale
     */
    protected function loadCatalogue($locale)
    {
        $catalogue = $this->translator->getCatalogue($locale);
        $catalogue->addCatalogue($this->adapter->loadCatalogue($locale));

        $this->catalogues[$locale] = $catalogue;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->translator->warmUp($cacheDir);
        $this->catalogues = [];
    }
}
