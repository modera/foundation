<?php

namespace Modera\BackendSecurityBundle\Tests\Unit\Contributions;

use Modera\BackendSecurityBundle\Contributions\ConfigMergersProvider;
use Modera\BackendSecurityBundle\Tests\Fixtures\App\Contributions\ClientDIContributor;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ConfigMergersProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testMerge()
    {
        $provider = new ConfigMergersProvider(
            new ClientDIContributor(),
            array(
                'hide_delete_user_functionality' => true,
            )
        );

        $actualResult = $provider->merge(array(
            'existing_key' => 'foobar',
        ));
        $expectedResult = array(
            'existing_key' => 'foobar',
            'modera_backend_security' => array(
                'hideDeleteUserFunctionality' => true,
                'sections' => array(
                    array(
                        'sectionConfig' => array(
                            'name' => 'section1',
                            'uiClass' => 'Some.ui.class',
                        ),
                        'menuConfig' => array(
                            'itemId' => 'section1',
                            'text' => 'Section 1',
                            'glyph' => 'icon-1',
                        )
                    )
                )
            ),
        );
        $this->assertSame($expectedResult, $actualResult);
    }
}
