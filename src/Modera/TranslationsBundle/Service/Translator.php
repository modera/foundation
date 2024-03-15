<?php

namespace Modera\TranslationsBundle\Service;

use Modera\TranslationsBundle\Compiler\Adapter\AdapterInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as DefaultTranslator;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Component\Translation\Translator as BaseTranslator;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class Translator extends BaseTranslator implements WarmableInterface
{
    private AdapterInterface $adapter;

    private DefaultTranslator $translator;

    public function __construct(
        AdapterInterface $adapter,
        MessageFormatterInterface $formatter,
        DefaultTranslator $translator,
        bool $debug = false
    ) {
        parent::__construct($translator->getLocale(), $formatter, null, $debug);

        $this->setFallbackLocales($translator->getFallbackLocales());

        $this->adapter = $adapter;
        $this->translator = $translator;
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
