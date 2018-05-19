<?php

namespace Modera\BackendSecurityBundle\Tests\Functional\DataMapper;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Alex Plaksin <alex.plaksin@modera.net>
 * @copyright 2016 Modera Foundation
 */
class UserDataMapperTest extends FunctionalTestCase
{
    /**
     * Phake Mock of Modera\BackendSecurityBundle\DataMapper\UserDataMapper.
     */
    private $mapperMock;

    public function doSetUp()
    {
        $mapperService = static::$container->get('sli.extjsintegration.entity_data_mapper');

        $this->mapperMock = \Phake::partialMock(
            'Modera\BackendSecurityBundle\DataMapper\UserDataMapper',
            $mapperService,
            static::$em
        );
    }

    public function testDataMapper_ExcludedFiled()
    {
        $mappedFields = \Phake::makeVisible($this->mapperMock)->getAllowedFields(User::clazz());

        $this->assertTrue(false === array_search('meta', $mappedFields));
    }

    public function testMapData_SetNewMeta()
    {
        $meta = array('newKey' => 'newVal');
        $params = array(
            'lastName' => 'LastName',
            'meta' => $meta,
        );
        $user = new User();

        $this->mapperMock->mapData($params, $user);

        $this->assertEquals('LastName', $user->getLastName());
        $this->assertEquals($meta, $user->getMeta());
    }

    public function testMapData_RewriteExistingMeta()
    {
        $meta = array('newKey' => 'newVal');
        $params = array(
            'lastName' => 'LastName',
            'meta' => $meta,
        );
        $user = new User();
        $user->setMeta(array('WillBeRewrited' => true));

        $this->mapperMock->mapData($params, $user);

        $this->assertEquals('LastName', $user->getLastName());
        $this->assertEquals($meta, $user->getMeta());
    }

    public function testMapData_ClearExisting()
    {
        $meta = array('WillBeErased' => true);
        $params = array(
            'lastName' => 'LastName',
            'meta' => '',
        );
        $user = new User();
        $user->setMeta($meta);

        $this->mapperMock->mapData($params, $user);

        $this->assertEquals('LastName', $user->getLastName());
        $this->assertEquals(array(), $user->getMeta());
    }

    public function testMapData_WillBeNotTouched()
    {
        $meta = array('WillExistsAfterMapping' => true);
        $params = array(
            'lastName' => 'LastName',
        );

        $user = new User();
        $user->setMeta($meta);

        $this->mapperMock->mapData($params, $user);

        $this->assertEquals('LastName', $user->getLastName());
        $this->assertEquals($meta, $user->getMeta());
    }
}
