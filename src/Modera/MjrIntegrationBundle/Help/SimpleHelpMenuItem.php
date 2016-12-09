<?php

namespace Modera\MjrIntegrationBundle\Help;

/**
 * Use methods {@link #createActivityAware} or {@link #createIntentAware} to create help menu items.
 *
 * @since 2.54.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class SimpleHelpMenuItem implements HelpMenuItemInterface
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $activityId;

    /**
     * @var array
     */
    private $activityParams = array();

    /**
     * @var string
     */
    private $intentId;

    /**
     * @var array
     */
    private $intentParams = array();

    /**
     * @var string
     */
    private $url;

    /**
     * Creates a menu item that will trigger activity activation when menu item is clicked by user.
     *
     * @param string $label
     * @param string $activityId
     * @param array  $activityParams
     *
     * @return HelpMenuItemInterface
     */
    public static function createActivityAware($label, $activityId, $activityParams = array())
    {
        $me = new static();
        $me->label = $label;
        $me->activityId = $activityId;
        $me->activityParams = $activityParams;

        return $me;
    }

    /**
     * Creates a menu item that will trigger intent dispatching when menu item is clicked by user.
     *
     * @param string $label
     * @param string $intentId
     * @param array  $intentParams
     *
     * @return HelpMenuItemInterface
     */
    public static function createIntentAware($label, $intentId, $intentParams = array())
    {
        $me = new static();
        $me->label = $label;
        $me->intentId = $intentId;
        $me->intentParams = $intentParams;

        return $me;
    }

    /**
     * Creates a menu item that will trigger opening a new URL.
     *
     * @param string $label
     * @param string $url
     *
     * @return HelpMenuItemInterface
     */
    public static function createUrlAware($label, $url)
    {
        $me = new static();
        $me->label = $label;
        $me->url = $url;

        return $me;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityId()
    {
        return $this->activityId;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityParams()
    {
        return $this->activityParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntentId()
    {
        return $this->intentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntentParams()
    {
        return $this->intentParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->url;
    }
}
