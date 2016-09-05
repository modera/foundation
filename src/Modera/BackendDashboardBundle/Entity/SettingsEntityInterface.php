<?php

namespace Modera\BackendDashboardBundle\Entity;

/**
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
interface SettingsEntityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param array $settings
     *
     * @return mixed
     */
    public function setDashboardSettings(array $settings);

    /**
     * @return array
     */
    public function getDashboardSettings();

    /**
     * @return string
     */
    public function describeEntity();
}
