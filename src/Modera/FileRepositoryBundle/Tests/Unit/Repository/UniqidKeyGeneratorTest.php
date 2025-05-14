<?php

namespace Modera\FileRepositoryBundle\Tests\Unit\Repository;

use Modera\FileRepositoryBundle\Repository\UniqidKeyGenerator;

class UniqidKeyGeneratorTest extends \PHPUnit\Framework\TestCase
{
    private \SplFileInfo $file;

    public function setUp(): void
    {
        $pathname = \sys_get_temp_dir().'/foo.txt';
        \file_put_contents($pathname, '');

        $this->file = new \SplFileInfo($pathname);
    }

    public function testWithExtension(): void
    {
        $g = new UniqidKeyGenerator(true);

        $generatedFilename = $g->generateStorageKey($this->file);

        $this->assertEquals('.txt', \substr($generatedFilename, -1 * \strlen('.txt')));
    }

    public function testWithoutExtension(): void
    {
        $g = new UniqidKeyGenerator();

        $filename = $g->generateStorageKey($this->file);

        $this->assertTrue('.'.\substr($filename, -1 * \strlen($this->file->getExtension())) != '.'.$this->file->getExtension());
    }
}
