<?php

namespace Modera\ExpanderBundle\Ext;

use Modera\ExpanderBundle\DependencyInjection\CompositeContributorsProviderCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class is used to describe your extension point purpose as well as encapsulates compiler-pass creation logic.
 *
 * @copyright 2024 Modera Foundation
 */
class ExtensionPoint
{
    private ?string $serviceId = null;

    private ?string $contributionTag = null;

    private ?string $category = null;

    private ?string $description = null;

    private ?string $detailedDescription = null;

    public function __construct(
        private string $id,
    ) {
    }

    public function createCompilerPass(): CompilerPassInterface
    {
        return new CompositeContributorsProviderCompilerPass(
            $this->getServiceId(),
            $this->getContributionTag(),
            $this,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getServiceId(): string
    {
        return $this->serviceId ?? $this->id.'_provider';
    }

    public function setServiceId(?string $serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    public function getContributionTag(): string
    {
        return $this->contributionTag ?? $this->id.'_provider';
    }

    public function setContributionTag(?string $contributionTag): self
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

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'serviceId' => $this->serviceId,
            'contributionTag' => $this->contributionTag,
            'category' => $this->category,
            'description' => $this->description,
            'detailedDescription' => $this->detailedDescription,
        ];
    }

    /**
     * @param array{
     *     'id': string,
     *     'serviceId'?: string,
     *     'contributionTag'?: string,
     *     'category'?: string,
     *     'description'?: string,
     *     'detailedDescription'?: string,
     * } $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->serviceId = $data['serviceId'] ?? null;
        $this->contributionTag = $data['contributionTag'] ?? null;
        $this->category = $data['category'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->detailedDescription = $data['detailedDescription'] ?? null;
    }
}
