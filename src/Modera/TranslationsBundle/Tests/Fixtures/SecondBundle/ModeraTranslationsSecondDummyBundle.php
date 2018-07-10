<?php

namespace Modera\TranslationsBundle\Tests\Fixtures\SecondBundle;

use Modera\TranslationsBundle\Tests\Fixtures\SecondBundle\DependencyInjection\ModeraTranslationsSecondDummyExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class ModeraTranslationsSecondDummyBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new ModeraTranslationsSecondDummyExtension());
    }
}
