<?php

namespace Modera\DynamicallyConfigurableMJRBundle\Tests\Unit\Contributions;

use Modera\ConfigBundle\Config\ConfigurationEntryDefinition;
use Modera\DynamicallyConfigurableMJRBundle\Contributions\ConfigEntriesProvider;
use Modera\DynamicallyConfigurableMJRBundle\ModeraDynamicallyConfigurableMJRBundle as Bundle;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ConfigEntriesProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetItems()
    {
        $provider = new ConfigEntriesProvider();

        /* @var ConfigurationEntryDefinition[] $items */
        $items = $provider->getItems();

        $this->assertEquals(5, count($items));

        $foundProperties = [];
        foreach ($items as $item) {
            $foundProperties[] = $item->getName();

            $this->assertEquals('general', $item->getCategory());
            $this->assertTrue('' != $item->getReadableName(), 'No readable name provided for '.$item->getName());
        }

        $this->assertTrue(in_array(Bundle::CONFIG_TITLE, $foundProperties));
        $this->assertTrue(in_array(Bundle::CONFIG_URL, $foundProperties));
        $this->assertTrue(in_array(Bundle::CONFIG_HOME_SECTION, $foundProperties));
        $this->assertTrue(in_array(Bundle::CONFIG_SKIN_CSS, $foundProperties));
        $this->assertTrue(in_array(Bundle::CONFIG_LOGO_URL, $foundProperties));
    }
}
