<?php

namespace Modera\TranslationsBundle\Handling;

use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2023 Modera Foundation
 */
class ResourcesTranslationHandler implements TranslationHandlerInterface
{
    protected ExtractorInterface $extractor;

    protected string $source;

    protected string $bundle;

    /**
     * @var string[]
     */
    protected iterable $resources = [];

    /**
     * @var string[]
     */
    protected array $strategies = [];

    /**
     * @param ?string[] $resources
     * @param ?string[] $strategies
     */
    public function __construct(
        ExtractorInterface $extractor,
        string $source,
        string $bundle,
        ?iterable $resources = null,
        ?array $strategies = null
    ) {
        $this->extractor = $extractor;
        $this->source = $source;
        $this->bundle = $bundle;
        $this->resources = $resources ?? [];
        $this->strategies = $strategies ?? [static::STRATEGY_SOURCE_TREE];
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
