<?php

namespace Modera\BackendSecurityBundle\Tests\Unit\Contributions;

use Modera\BackendSecurityBundle\Contributions\ConfigMergersProvider;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ConfigMergersProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        $provider = new ConfigMergersProvider(
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
            ),
        );
        $this->assertSame($expectedResult, $actualResult);
    }
}