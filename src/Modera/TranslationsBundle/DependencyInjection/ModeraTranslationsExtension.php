<?php

namespace Modera\TranslationsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ModeraTranslationsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (\class_exists('Symfony\Component\Console\Application')) {
            try {
                $loader->load('console.xml');
            } catch (\Exception $e) {
            }
        }

        /** @var string $projectDir */
        $projectDir = $container->getParameter('kernel.project_dir');

        $translationsDir = \join(\DIRECTORY_SEPARATOR, [$projectDir, 'app', 'Resources', 'translations']);
        if ($container->hasParameter('modera.translations_dir')) {
            $translationsDir = $container->getParameter('modera.translations_dir');
        } elseif ($container->hasParameter('translator.default_path')) {
            $translationsDir = $container->getParameter('translator.default_path');
        }

        $translationWriterAdapter = $container->findDefinition('modera_translations.compiler.adapter.translation_writer_adapter');
        $translationWriterAdapter->replaceArgument(1, $container->getParameterBag()->resolveValue($translationsDir));
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasParameter('modera.translations_dir')) {
            if ($container->hasParameter('modera.expose_translations_dir')) {
                $value = $container->getParameter('modera.expose_translations_dir');
                $expose = true === $container->getParameterBag()->resolveValue($value);

                if ($expose) {
                    $value = $container->getParameter('modera.translations_dir');
                    $dir = $container->getParameterBag()->resolveValue($value);

                    $fs = new Filesystem();
                    try {
                        if (!$fs->exists($dir)) {
                            $fs->mkdir($dir);
                        }
                    } catch (IOExceptionInterface $e) {
                        throw new \RuntimeException(\sprintf('An error occurred while creating translations directory at %s', $e->getPath()));
                    }

                    $container->prependExtensionConfig('framework', [
                        'translator' => [
                            'paths' => [
                                $dir,
                            ],
                        ],
                    ]);
                }
            }
        }
    }
}
