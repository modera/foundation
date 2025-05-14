<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Modera\TranslationsBundle\Compiler\Adapter\AdapterInterface;
use Modera\TranslationsBundle\Compiler\Adapter\TranslationWriterAdapter;
use Modera\TranslationsBundle\Compiler\TranslationsCompiler;
use Modera\TranslationsBundle\Handling\JsonFileTranslationHandler;
use Modera\TranslationsBundle\Handling\PhpClassesTranslationHandler;
use Modera\TranslationsBundle\Handling\TemplateTranslationHandler;
use Modera\TranslationsBundle\Service\TranslationHandlersChain;
use Modera\TranslationsBundle\Service\Translator;
use Modera\TranslationsBundle\TokenExtraction\JsonFileExtractor;
use Modera\TranslationsBundle\TokenExtraction\PhpClassTokenExtractor;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(TranslationWriterAdapter::class)
        ->arg('$writer', service('translation.writer'))
        ->arg('$translationsDir', abstract_arg('translations dir'))
        ->arg('$cacheDir', param('kernel.cache_dir').'/translations')
    ;
    $services->alias(AdapterInterface::class, TranslationWriterAdapter::class);

    $services->set(TranslationsCompiler::class);

    $services->set(JsonFileTranslationHandler::class)
        ->abstract()
        ->autoconfigure(false)
        ->arg('$extractor', service(JsonFileExtractor::class))
    ;
    // TODO: remove, BC
    $services->set('modera_translations.handling.json_file_translation_handler', JsonFileTranslationHandler::class)
        ->abstract()
        ->autowire(false)
        ->autoconfigure(false)
        ->args([
            service(JsonFileExtractor::class),
        ])
    ;

    $services->set(PhpClassesTranslationHandler::class)
        ->abstract()
        ->autoconfigure(false)
        ->arg('$kernel', service('kernel'))
        ->arg('$loader', service('translation.reader'))
        ->arg('$extractor', service(PhpClassTokenExtractor::class))
    ;
    // TODO: remove, BC
    $services->set('modera_translations.handling.php_classes_translation_handler', PhpClassesTranslationHandler::class)
        ->abstract()
        ->autowire(false)
        ->autoconfigure(false)
        ->args([
            service('kernel'),
            service('translation.reader'),
            service(PhpClassTokenExtractor::class),
        ])
    ;

    $services->set(TemplateTranslationHandler::class)
        ->abstract()
        ->autoconfigure(false)
        ->arg('$kernel', service('kernel'))
        ->arg('$loader', service('translation.reader'))
        ->arg('$extractor', service('translation.extractor'))
    ;
    // TODO: remove, BC
    $services->set('modera_translations.handling.template_translation_handler', TemplateTranslationHandler::class)
        ->abstract()
        ->autowire(false)
        ->autoconfigure(false)
        ->args([
            service('kernel'),
            service('translation.reader'),
            service('translation.extractor'),
        ])
    ;

    $services->set(TranslationHandlersChain::class);

    $services->set(Translator::class)
        ->public()
        ->arg('$formatter', service('translator.formatter'))
        ->arg('$translator', service('translator.default'))
        ->arg('$debug', param('kernel.debug'))
        ->tag('kernel.locale_aware')
    ;

    $services->set(JsonFileExtractor::class);

    $services->set(PhpClassTokenExtractor::class);
};
