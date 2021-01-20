<?php

namespace Modera\BackendSecurityBundle\Section;

/**
 * @author    Artem Brovko <artem.brovko@modera.com>
 * @copyright 2021 Modera Foundation
 */
class Section implements SectionInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $iconCls;

    /**
     * @var string
     */
    private $uiClass;

    /**
     * @param string $id
     * @param string $title
     * @param string $iconCls
     * @param string $uiClass
     */
    public function __construct($id, $title, $iconCls, $uiClass)
    {
        $this->id = $id;
        $this->title = $title;
        $this->iconCls = $iconCls;
        $this->uiClass = $uiClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getIconCls()
    {
        return $this->iconCls;
    }

    /**
     * {@inheritdoc}
     */
    public function getUiClass()
    {
        return $this->uiClass;
    }
}
