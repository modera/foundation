<?php

namespace Modera\MjrIntegrationBundle\Help;

/**
 * Use methods {@link #createActivityAware} or {@link #createIntentAware} to create help menu items.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class SimpleHelpMenuItem implements HelpMenuItemInterface
{
    private string $label;

    private ?string $activityId = null;

    /**
     * @var mixed[]
     */
    private array $activityParams = [];

    private ?string $intentId = null;

    /**
     * @var mixed[]
     */
    private array $intentParams = [];

    private ?string $url = null;

    /**
     * Creates a menu item that will trigger activity activation when menu item is clicked by user.
     *
     * @param mixed[] $activityParams
     */
    public static function createActivityAware(string $label, string $activityId, array $activityParams = []): self
    {
        $me = new self();
        $me->label = $label;
        $me->activityId = $activityId;
        $me->activityParams = $activityParams;

        return $me;
    }

    /**
     * Creates a menu item that will trigger intent dispatching when menu item is clicked by user.
     *
     * @param mixed[] $intentParams
     */
    public static function createIntentAware(string $label, string $intentId, array $intentParams = []): self
    {
        $me = new self();
        $me->label = $label;
        $me->intentId = $intentId;
        $me->intentParams = $intentParams;

        return $me;
    }

    /**
     * Creates a menu item that will trigger opening a new URL.
     */
    public static function createUrlAware(string $label, string $url): self
    {
        $me = new self();
        $me->label = $label;
        $me->url = $url;

        return $me;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function getActivityParams(): array
    {
        return $this->activityParams;
    }

    public function getIntentId(): ?string
    {
        return $this->intentId;
    }

    public function getIntentParams(): array
    {
        return $this->intentParams;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
