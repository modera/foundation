<?php

namespace Modera\FoundationBundle\Translation;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Translation\Translator;

/**
 * You can use this helper to translate messages right from you PHP classes. When this helper is used
 * then translated tokens will be detected by {@class \Modera\TranslationsBundle\TokenExtraction\PhpClassTokenExtractor}.
 *
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class T
{
    /**
     * Property is going to be dynamically inject by \Modera\TranslationsBundle\ModeraTranslationsBundle::boot() method.
     *
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @see \Symfony\Contracts\Translation\TranslatorInterface::trans
     *
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    public static function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (self::$container) {
            /* @var TranslatorInterface $translator */
            $translator = self::$container->get('translator');

            return $translator->trans($id, $parameters, $domain, $locale);
        }

        return $id;
    }

    /**
     * @deprecated Use native ::class property
     *
     * @return string
     */
    public static function clazz()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated. Use native ::class property.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return get_called_class();
    }
}
