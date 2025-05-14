<?php

namespace Modera\ExpanderBundle\Generation;

use Modera\ExpanderBundle\Ext\ExtensionPoint;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Implementations are responsible for creating/updating files that are needed to create a contribution to a given
 * extension point.
 *
 * @copyright 2024 Modera Foundation
 */
interface ContributionGeneratorInterface
{
    public function generate(
        BundleInterface $bundle,
        ExtensionPoint $extensionPoint,
        InputInterface $input,
        OutputInterface $output,
        ?HelperSet $helperSet = null,
    ): void;
}
