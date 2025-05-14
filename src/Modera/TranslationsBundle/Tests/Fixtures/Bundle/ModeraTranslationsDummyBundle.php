<?php

namespace Modera\TranslationsBundle\Tests\Fixtures\Bundle;

use Modera\TranslationsBundle\Tests\Fixtures\Bundle\DependencyInjection\ModeraTranslationsDummyExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ModeraTranslationsDummyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerExtension(new ModeraTranslationsDummyExtension());
    }
}
