<?php

namespace Modera\TranslationsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ModeraTranslationsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasParameter('modera.translations_dir')) {
            $value = $container->getParameter('modera.translations_dir');
            $dir = $container->getParameterBag()->resolveValue($value);

            $fs = new Filesystem();
            try {
                if (!$fs->exists($dir)) {
                    $fs->mkdir($dir);
                    $fs->chmod($dir, 0777);
                }
            } catch (IOExceptionInterface $e) {
                throw new \RuntimeException(
                    'An error occurred while creating translations directory at '.$e->getPath()
                );
            }

            $container->prependExtensionConfig('framework', array(
                'translator' => array(
                    'paths' => array(
                        $dir,
                    ),
                ),
            ));
        }
    }
}
