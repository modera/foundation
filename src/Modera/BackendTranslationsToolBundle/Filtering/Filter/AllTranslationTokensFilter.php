<?php

namespace Modera\BackendTranslationsToolBundle\Filtering\Filter;

/**
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
