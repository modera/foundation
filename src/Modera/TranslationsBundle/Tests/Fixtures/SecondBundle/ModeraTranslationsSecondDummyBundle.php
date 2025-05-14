<?php

namespace Modera\TranslationsBundle\Tests\Fixtures\SecondBundle;

use Modera\TranslationsBundle\Tests\Fixtures\SecondBundle\DependencyInjection\ModeraTranslationsSecondDummyExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ModeraTranslationsSecondDummyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerExtension(new ModeraTranslationsSecondDummyExtension());
    }
}
