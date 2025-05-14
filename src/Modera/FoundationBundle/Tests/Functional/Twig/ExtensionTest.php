<?php

namespace Modera\FoundationBundle\Tests\Functional\Twig;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\FoundationBundle\Twig\Extension;

class ExtensionTest extends FunctionalTestCase
{
    public function testHasExtension(): void
    {
        /** @var \Twig\Environment $twig */
        $twig = self::getContainer()->get('twig');

        $this->assertTrue($twig->hasExtension(Extension::class));
    }

    public function testHasFilters(): void
    {
        /** @var \Twig\Environment $twig */
        $twig = self::getContainer()->get('twig');

        $this->assertInstanceOf('Twig\TwigFilter', $twig->getFilter('mf_prepend_every_line'));
        $this->assertInstanceOf('Twig\TwigFilter', $twig->getFilter('mf_modification_time'));
    }
}
