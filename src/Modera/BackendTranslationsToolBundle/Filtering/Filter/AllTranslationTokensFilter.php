<?php

namespace Modera\BackendTranslationsToolBundle\Filtering\Filter;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class AllTranslationTokensFilter extends AbstractTranslationTokensFilter
{
    public function getId(): string
    {
        return 'all';
    }

    public function getName(): string
    {
        return 'All';
    }
}
