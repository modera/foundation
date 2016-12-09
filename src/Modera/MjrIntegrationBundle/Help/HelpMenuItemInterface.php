<?php

namespace Modera\MjrIntegrationBundle\Help;

/**
 * Help menu item will trigger either action through activating activity, dispatching intent OR opening a provided
 * URL, but not all at the same time.
 *
 * @since 2.54.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
interface HelpMenuItemInterface
{
    /**
     * A text that will be displayed in a menu when help menu is activated.
     *
     * @return string
     */
    public function getLabel();

    /**
     * ID of activity to activate.
     *
     * @return string
     */
    public function getActivityId();

    /**
     * Optional parameters that activity should activated with.
     *
     * @return array
     */
    public function getActivityParams();

    /**
     * ID of intent that will be dispached.
     *
     * @return string
     */
    public function getIntentId();

    /**
     * Optional parameters that the intent will be dispatched with.
     *
     * @return array
     */
    public function getIntentParams();

    /**
     * URL that can be opened (in a new tab/window, depends on a user browser preferences).
     *
     * @return string
     */
    public function getUrl();
}
