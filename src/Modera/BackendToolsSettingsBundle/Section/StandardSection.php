<?php

namespace Modera\BackendToolsSettingsBundle\Section;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class StandardSection implements SectionInterface
{
    private $id;
    private $name;
    private $activityClass;
    private $glyph;
    private $meta;

    /**
     * @param string $id
     * @param string $name
     * @param string $activityClass
     * @param array  $meta
     */
    public function __construct($id, $name, $activityClass, $glyph = null, array $meta = array())
    {
        $this->id = $id;
        $this->name = $name;
        $this->activityClass = $activityClass;
        $this->glyph = $glyph;
        $this->meta = $meta;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityClass()
    {
        return $this->activityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlyph()
    {
        return $this->glyph;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
