<?php

namespace Modera\BackendSecurityBundle\Tests\Unit\Contributions;

use Modera\BackendSecurityBundle\Contributions\ConfigMergersProvider;
use Modera\BackendSecurityBundle\Tests\Fixtures\App\Contributions\ClientDIContributor;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ConfigMergersProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        $provider = new ConfigMergersProvider(
            new ClientDIContributor(),
            array(
                'hide_delete_user_functionality' => 'yoyo',
            )
        );

        $actualResult = $provider->merge(array(
            'existing_key' => 'foobar',
        ));
        $expectedResult = array(
            'existing_key' => 'foobar',
            'modera_backend_security' => array(
                'hideDeleteUserFunctionality' => 'yoyo',
                'sections' => array(
                    array(
                        'sectionConfig' => array(
                            'name' => 'section1',
                            'uiClass' => 'Some.ui.class',
                        ),
                        'menuConfig' => array(
                            'itemId' => 'section1',
                            'text' => 'Section 1',
                            'iconCls' => 'icon-1',
                            'tid' => 'section1SectionButton',
                        )
                    )
                )
            ),
        );
        $this->assertSame($expectedResult, $actualResult);
    }
}
