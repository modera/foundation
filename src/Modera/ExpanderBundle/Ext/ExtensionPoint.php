<?php

namespace Modera\ExpanderBundle\Ext;

use Modera\ExpanderBundle\DependencyInjection\CompositeContributorsProviderCompilerPass;
use Modera\ExpanderBundle\Generation\ContributionGeneratorInterface;
use Modera\ExpanderBundle\Generation\StandardContributionGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class is used to describe your extension point purpose as well as encapsulates compiler-pass
 * creation logic.
 */
class ExtensionPoint
{
    private string $id;

    private string $contributionTag;

    private ?string $category = null;

    private ?string $description = null;

    private ?string $detailedDescription = null;

    /**
     * @var array<string, mixed>
     */
    private array $generatorConfig;

    /**
     * @param array<string, mixed> $generatorConfig
     */
    public function __construct(string $id, array $generatorConfig = [])
    {
        $this->id = $id;
        $this->contributionTag = $id.'_provider';
        $this->generatorConfig = $generatorConfig;
    }

    public function createCompilerPass(): CompilerPassInterface
    {
        return new CompositeContributorsProviderCompilerPass($this->contributionTag, $this->contributionTag, $this);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContributionTag(): string
    {
        return $this->contributionTag;
    }

    public function setContributionTag(string $contributionTag): self
    {
        $this->contributionTag = $contributionTag;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDetailedDescription(): ?string
    {
        return $this->detailedDescription;
    }

    public function setDetailedDescription(?string $detailedDescription): self
    {
        $this->detailedDescription = $detailedDescription;

        return $this;
    }

    public function getContributionGenerator(): ?ContributionGeneratorInterface
    {
        return new StandardContributionGenerator($this->generatorConfig);
    }
}
