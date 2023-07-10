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

    protected iterable $resources = [];

    protected array $strategies = [];

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
        $this->strategies = $strategies ?? [ static::STRATEGY_SOURCE_TREE ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBundleName(): string
    {
        return $this->bundle;
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    /**
     * {@inheritdoc}
     */
    public function getSources(): array
    {
        return array($this->source);
    }

    /**
     * {@inheritdoc}
     */
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

    protected function getResources(): iterable
    {
        return $this->resources;
    }
}
