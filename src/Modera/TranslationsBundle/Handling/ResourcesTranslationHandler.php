<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @copyright 2023 Modera Foundation
 */
class ResourcesTranslationHandler implements TranslationHandlerInterface
{
    /**
     * @param string[] $resources
     * @param string[] $strategies
     */
    public function __construct(
        protected readonly ExtractorInterface $extractor,
        protected readonly string $source,
        protected readonly string $bundle,
        protected readonly iterable $resources = [],
        protected readonly array $strategies = [self::STRATEGY_SOURCE_TREE],
    ) {
    }

    public function getBundleName(): string
    {
        return $this->bundle;
    }

    public function getStrategies(): array
    {
        return $this->strategies;
    }

    public function getSources(): array
    {
        return [$this->source];
    }

    public function extract(string $source, string $locale): ?MessageCatalogueInterface
    {
        if (!$this->isSourceAvailable($source)) {
            return null;
        }

        $extractedCatalogue = new MessageCatalogue($locale);
        $this->extractor->extract($this->getResources(), $extractedCatalogue);

        return $extractedCatalogue;
    }

    protected function isSourceAvailable(string $source): bool
    {
        return $this->source === $source;
    }

    /**
     * @return string[]
     */
    protected function getResources(): iterable
    {
        return $this->resources;
    }
}
