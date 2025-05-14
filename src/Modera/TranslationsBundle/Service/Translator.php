<?php

namespace Modera\TranslationsBundle\Service;

use Modera\TranslationsBundle\Compiler\Adapter\AdapterInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as DefaultTranslator;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Component\Translation\Translator as BaseTranslator;

/**
 * @copyright 2019 Modera Foundation
 */
class Translator extends BaseTranslator implements WarmableInterface
{
    public function __construct(
        private readonly AdapterInterface $adapter,
        MessageFormatterInterface $formatter,
        private readonly DefaultTranslator $translator,
        bool $debug = false,
    ) {
        parent::__construct($translator->getLocale(), $formatter, null, $debug);

        $this->setFallbackLocales($translator->getFallbackLocales());
    }

    protected function loadCatalogue(string $locale): void
    {
        $catalogue = $this->translator->getCatalogue($locale);
        $catalogue->addCatalogue($this->adapter->loadCatalogue($locale));

        $this->catalogues[$locale] = $catalogue;
    }

    public function warmUp(string $cacheDir): array
    {
        $arr = $this->translator->warmUp($cacheDir);
        $this->catalogues = [];

        return $arr;
    }
}
