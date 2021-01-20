<?php

namespace Modera\BackendSecurityBundle\Contributions;

use Modera\BackendSecurityBundle\Section\SectionInterface;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Config\ConfigMergerInterface;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ConfigMergersProvider implements ContributorInterface, ConfigMergerInterface
{
    /**
     * @var array
     */
    private $semanticConfig = array();

    /**
     * @var ContributorInterface
     */
    private $sectionsProvider;

    /**
     * @param ContributorInterface $sectionsProvider
     * @param array $semanticConfig
     */
    public function __construct(ContributorInterface $sectionsProvider, array $semanticConfig = array())
    {
        $this->semanticConfig = $semanticConfig;
        $this->sectionsProvider = $sectionsProvider;
    }

    /**
     * @param array $currentConfig
     *
     * @return array
     */
    public function merge(array $currentConfig)
    {
        $currentConfig = array_merge($currentConfig, array(
            'modera_backend_security' => array(
                'hideDeleteUserFunctionality' => $this->semanticConfig['hide_delete_user_functionality'],
                'sections' => array()
            ),
        ));

        foreach($this->sectionsProvider->getItems() as $section) {
            if ($section instanceof SectionInterface) {
                $currentConfig['modera_backend_security']['sections'][] = array(
                    'sectionConfig' => array(
                        'name' => $section->getId(),
                        'uiClass' => $section->getUiClass(),
                    ),
                    'menuConfig' => array(
                        'itemId' => $section->getId(),
                        'text' => $section->getTitle(),
                        'iconCls' => $section->getIconCls(),
                        'tid' => $section->getId() . 'SectionButton'
                    )
                );
            }
        }

        return $currentConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array($this);
    }
}
