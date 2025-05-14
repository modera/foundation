<?php

namespace Modera\FoundationBundle\Tests\Unit\Twig;

use Modera\FoundationBundle\Twig\Extension;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class ExtensionTest extends \PHPUnit\Framework\TestCase
{
    private Extension $ext;

    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('root', null, [
            'public' => [
                'js' => [
                    'moment.js' => 'foo bar',
                ],
            ],
        ]);

        $this->ext = new Extension($this->root->url().'/public');
    }

    public function testFilterPrependEveryLine(): void
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

    public function testFilterModificationTime(): void
    {
        $mtime = filemtime($this->root->url().'/public/js/moment.js');

        $this->assertEquals(\sprintf('js/moment.js?%s', $mtime), $this->ext->filter_modification_time('js/moment.js'));
        $this->assertEquals(
            'js/foo.js',
            $this->ext->filter_modification_time('js/foo.js'),
            'Non existing files should not have modification time appended',
        );
    }
}
