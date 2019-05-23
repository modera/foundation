<?php

namespace Modera\ModuleBundle\Manipulator;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Changes the PHP code of a Kernel.
 *
 * @deprecated The KernelManipulator is deprecated and will be removed in version 4.0
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class KernelManipulator
{
    protected $kernel;
    protected $reflected;
    protected $tokens;
    protected $line;

    protected $doc =
<<<'DOC'
    /**
     * Auto generated, do not change!
     *
     * Makes it possible to dynamically inject bundles to kernel.
     *
     * @param array $bundles
     *
     * @return array
     */
DOC;

    // MPFE-757
    // it is very important that in a snippet below we use "require" function to load external PHP file
    // because when cache:clear command is used, it seems that Symfony creates two instances of Kernel
    // class, and first time when Kernel class is created, "require_once" will load external fine well, but in second
    // instance of Kernel require_once will do nothing, because file has already been loaded during this PHP
    // interpreter session and this results in things such that Symfony doesn't see routes registered
    // by bundles from $moduleBundlesFile file.
    protected $template =
<<<TEMPLATE
    private function registerModuleBundles(array \$bundles)
    {
        \$moduleBundlesFile = __DIR__ . '/%s';
        if (file_exists(\$moduleBundlesFile)) {
            \$moduleBundles = require \$moduleBundlesFile;
            if (is_array(\$moduleBundles)) {
                \$resolver = new \Modera\\ModuleBundle\ModuleHandling\BundlesResolver();

                \$bundles = \$resolver->resolve(
                    array_merge(\$bundles, \$moduleBundles), \$this
                );
            }
        }

        return \$bundles;
    }
TEMPLATE;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->reflected = new \ReflectionObject($kernel);
    }

    /**
     * @param string $bundlesFilename A name of a file which will hold dynamically instantiated bundles
     *
     * @return bool
     */
    public function addCode($bundlesFilename)
    {
        if (!$this->reflected->getFilename()) {
            return false;
        }

        $src = file($this->reflected->getFilename());

        try {
            $method = $this->reflected->getMethod('registerModuleBundles');

            $lines = array_merge(
                array(
                    str_replace(
                        'return $bundles;',
                        'return $this->registerModuleBundles($bundles);',
                        implode('', array_slice($src, 0, $method->getStartLine() - 1))
                    ),
                ),
                array(
                    sprintf($this->template, $bundlesFilename),
                    "\n",
                ),
                array_slice($src, $method->getEndLine())
            );
        } catch (\ReflectionException $e) {
            $line = count($src) - 1;
            while ($line > 0) {
                if (trim($src[$line]) == '}') {
                    break;
                }
                --$line;
            }

            $lines = array_merge(
                array(
                    str_replace(
                        'return $bundles;',
                        'return $this->registerModuleBundles($bundles);',
                        implode('', array_slice($src, 0, $line))
                    ),
                ),
                array(
                    "\n",
                    $this->doc,
                    "\n",
                    sprintf($this->template, $bundlesFilename),
                    "\n",
                ),
                array_slice($src, $line)
            );
        }

        file_put_contents($this->reflected->getFilename(), implode('', $lines));

        return true;
    }

    /**
     * Sets the code to manipulate.
     *
     * @param array $tokens An array of PHP tokens
     * @param int   $line   The start line of the code
     */
    protected function setCode(array $tokens, $line = 0)
    {
        $this->tokens = $tokens;
        $this->line = $line;
    }

    /**
     * Gets the next token.
     *
     * @return string|null
     */
    protected function next()
    {
        while ($token = array_shift($this->tokens)) {
            $this->line += substr_count($this->value($token), "\n");

            if (is_array($token) && in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
                continue;
            }

            return $token;
        }
    }

    /**
     * Peeks the next token.
     *
     * @param int $nb
     *
     * @return string|null
     */
    protected function peek($nb = 1)
    {
        $i = 0;
        $tokens = $this->tokens;
        while ($token = array_shift($tokens)) {
            if (is_array($token) && in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
                continue;
            }

            ++$i;
            if ($i == $nb) {
                return $token;
            }
        }
    }

    /**
     * Gets the value of a token.
     *
     * @param string|string[] $token The token value
     *
     * @return string
     */
    protected function value($token)
    {
        return is_array($token) ? $token[1] : $token;
    }
}
