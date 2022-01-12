<?php

namespace Modera\FoundationBundle\Tests\Unit\Twig;

use Modera\FoundationBundle\Twig\Extension;
use org\bovigo\vfs\vfsStream;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class ExtensionTest extends \PHPUnit\Framework\TestCase
{
    /* @var Extension $ext */
    private $ext;

    /* @var \org\bovigo\vfs\vfsStreamDirectory $root */
    private $root;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->root = vfsStream::setup('root', null, array(
            'public' => array(
                'js' => array(
                    'moment.js' => 'foo bar'
                ),
            ),
        ));

        $this->ext = new Extension($this->root->url() . '/public');
    }

    public function testFilter_prepend_every_line()
    {
        $input = <<<'TEXT'
foo
bar
TEXT;

        $expectedOutput = <<<'TEXT'
   foo
   bar
TEXT;

        $this->assertEquals($expectedOutput, $this->ext->filter_prepend_every_line($input, 3));

        // ---

        $input = <<<'TEXT'
 foo
  bar
TEXT;

        $expectedOutput = <<<'TEXT'
---- foo
----  bar
TEXT;
        $this->assertEquals($expectedOutput, $this->ext->filter_prepend_every_line($input, 4, '-'));

        // --

        $input = <<<'JSON'
{
    foo: {}
}
JSON;

        $expectedOutput = <<<'JSON'
{
        foo: {}
    }
JSON;

        $this->assertEquals($expectedOutput, $this->ext->filter_prepend_every_line($input, 4, ' ', true));
    }

    public function testFilter_modification_time()
    {
        $mtime = filemtime($this->root->url().'/public/js/moment.js');

        $this->assertEquals("js/moment.js?$mtime", $this->ext->filter_modification_time('js/moment.js'));
        $this->assertEquals(
            'js/foo.js',
            $this->ext->filter_modification_time('js/foo.js'),
            'Non existing files should not have modification time appended'
        );
    }
}
