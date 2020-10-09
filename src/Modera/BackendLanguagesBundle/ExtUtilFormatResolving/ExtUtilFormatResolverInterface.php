<?php

namespace Modera\BackendLanguagesBundle\ExtUtilFormatResolving;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2020 Modera Foundation
 */
interface ExtUtilFormatResolverInterface
{
    /**
     * @param string $locale
     * @param array $config
     * @return array
     */
    public function resolveExtUtilFormat($locale, array $config = array());
}
