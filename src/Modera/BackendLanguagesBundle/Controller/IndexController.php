<?php

namespace Modera\BackendLanguagesBundle\Controller;

use Sli\ExtJsLocalizationBundle\Controller\IndexController as Controller;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class IndexController extends Controller
{
    /**
     * {@inheritdoc}
     */
    protected function getTemplate()
    {
        return 'ModeraBackendLanguagesBundle:Index:compile.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTranslationsDir()
    {
        if ($this->container->hasParameter('modera.translations_dir')) {
            return $this->container->getParameter('modera.translations_dir');
        }

        return parent::getTranslationsDir();
    }
}
