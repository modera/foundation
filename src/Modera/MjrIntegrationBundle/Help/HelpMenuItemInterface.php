<?php

namespace Modera\MjrIntegrationBundle\Help;

/**
 * Help menu item will trigger either action through activating activity, dispatching intent OR opening a provided
 * URL, but not all at the same time.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
interface HelpMenuItemInterface
{
    /**
     * A text that will be displayed in a menu when help menu is activated.
     */
    public function getLabel(): string;

    /**
     * ID of activity to activate.
     */
    public function getActivityId(): ?string;

    /**
     * Optional parameters that activity should be activated with.
     *
     * @return mixed[]
     */
    public function getActivityParams(): array;

    /**
     * ID of intent that will be dispatched.
     */
    public function getIntentId(): ?string;

    /**
     * Optional parameters that the intent will be dispatched with.
     *
     * @return mixed[]
     */
    public function getIntentParams(): array;

    /**
     * URL that can be opened (in a new tab/window, depends on a user browser preferences).
     */
    public function getUrl(): ?string;
}
