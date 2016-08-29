<?php

namespace Modera\BackendDashboardBundle\Dashboard;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SimpleDashboard.
 *
 * @author    Alex Rudakov <alexandr.rudakov@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SimpleDashboard implements DashboardInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $uiClass;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $icon;

    /**
     * @param string $name        Technical name of dashboard
     * @param string $label       Human readable label
     * @param string $uiClass     ExtJs class that provide ui (Derivative of Ext.container.Container or similar)
     * @param string $description
     * @param string $icon
     */
    public function __construct($name, $label, $uiClass, $description = '', $icon = 'modera-backend-dashboard-default-icon')
    {
        $this->label = $label;
        $this->name = $name;
        $this->uiClass = $uiClass;
        $this->description = $description;
        $this->icon = $icon;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * {@inheritdoc}
     */
    public function getUiClass()
    {
        return $this->uiClass;
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
    public function isAllowed(ContainerInterface $container)
    {
        return true; // whatever
    }
}
