<?php

namespace Modera\MjrIntegrationBundle\Contributions;

use Modera\ExpanderBundle\Ext\AsContributorFor;
use Modera\ExpanderBundle\Ext\ContributorInterface;
use Modera\MjrIntegrationBundle\Model\FontAwesome;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @copyright 2014 Modera Foundation
 */
#[AsContributorFor('modera_mjr_integration.css_resources')]
class CssResourcesProvider implements ContributorInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getItems(): array
    {
        return \array_merge(FontAwesome::cssResources(), [
            $this->urlGenerator->generate('modera_font_awesome_css'),
        ]);
    }
}
