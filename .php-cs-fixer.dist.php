<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude([
        'tools',
        'vendor',
        'src/Modera/MjrIntegrationBundle/Resources/cache/font-awesome',
        'src/Modera/TranslationsBundle/Tests/Unit/TokenExtraction/dummy-classes',
    ])
    ->in(__DIR__)
    //->notPath('#src/Modera/.*/Tests#')
;

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
    ])
    ->setUsingCache(false)
    ->setFinder($finder)
;
