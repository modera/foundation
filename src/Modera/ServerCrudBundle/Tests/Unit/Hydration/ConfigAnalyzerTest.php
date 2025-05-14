<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Hydration;

use Modera\ServerCrudBundle\Hydration\ConfigAnalyzer;
use Modera\ServerCrudBundle\Hydration\HydrationProfile;
use Modera\ServerCrudBundle\Hydration\UnknownHydrationGroupException;
use Modera\ServerCrudBundle\Hydration\UnknownHydrationProfileException;

class ConfigAnalyzerTest extends \PHPUnit\Framework\TestCase
{
    private ConfigAnalyzer $config;

    private array $rawConfig;

    public function setUp(): void
    {
        $this->rawConfig = [
            'groups' => [
                'form' => [
                    'title', 'body',
                ],
                'author' => [
                    'firstname' => 'author.firstname',
                    'lastname' => 'author.lastname',
                ],
                'list' => function (Article $e) {
                    return [
                        'title' => \substr($e->title, 0, 10),
                        'body' => \substr($e->body, 0, 10),
                    ];
                },
            ],
            'profiles' => [
                'form' => HydrationProfile::create()->useGroups(['form', 'author']),
                'mixed' => ['form', 'author', 'list'],
                'list',
            ],
        ];

        $this->config = new ConfigAnalyzer($this->rawConfig);
    }

    public function testGetProfile(): void
    {
        $result = $this->config->getProfileDefinition('form');

        $this->assertInstanceOf(HydrationProfile::class, $result);
        $this->assertSame($this->rawConfig['profiles']['form'], $result);
    }

    public function testGetGroupProfileWithShortDeclaration(): void
    {
        /** @var HydrationProfile $result */
        $result = $this->config->getProfileDefinition('mixed');

        $this->assertInstanceOf(HydrationProfile::class, $result);
        $this->assertTrue($result->isGroupingNeeded());

        $groups = $result->getGroups();

        $this->assertTrue(\in_array('form', $groups));
        $this->assertTrue(\in_array('author', $groups));
        $this->assertTrue(\in_array('list', $groups));
    }

    public function testGetGroupProfileWhenProfileNameMatchesGroup(): void
    {
        /** @var HydrationProfile $result */
        $result = $this->config->getProfileDefinition('list');

        $this->assertInstanceOf(HydrationProfile::class, $result);
        $this->assertFalse($result->isGroupingNeeded());
        $this->assertSame(['list'], $result->getGroups());
    }

    public function testGetProfileWhenItDoesntExist(): void
    {
        $thrownException = null;
        try {
            $this->config->getProfileDefinition('blah');
        } catch (UnknownHydrationProfileException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertEquals('blah', $thrownException->getProfileName());
    }

    public function testGetGroupDefinition(): void
    {
        $result = $this->config->getGroupDefinition('author');

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('firstname', $result);
        $this->assertArrayHasKey('lastname', $result);
        $this->assertEquals('author.firstname', $result['firstname']);
        $this->assertEquals('author.lastname', $result['lastname']);
    }

    public function testGetGroupDefinitionWhenItDoestnExist(): void
    {
        $thrownException = null;
        try {
            $this->config->getGroupDefinition('blah');
        } catch (UnknownHydrationGroupException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertEquals('blah', $thrownException->getGroupName());
    }
}
