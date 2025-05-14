<?php

namespace Modera\ServerCrudBundle\Tests\Unit\QueryBuilder\Parsing;

use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filter;

class FilterTest extends \PHPUnit\Framework\TestCase
{
    public function testIsValid()
    {
        $f = new Filter(['property' => 'user.firstname', 'value' => 'eq:John']);

        $this->assertTrue($f->isValid());

        $f = new Filter(['property' => 'username', 'value' => 'eq:']);

        $this->assertTrue($f->isValid());

        $f = new Filter(['value' => 'eq:']);

        $this->assertFalse($f->isValid());

        $f = new Filter(['property' => 'username']);

        $this->assertFalse($f->isValid());

        $f = new Filter([]);

        $this->assertFalse($f->isValid());

        $f = new Filter(['property' => 'user', 'value' => 'isNull']);

        $this->assertTrue($f->isValid());

        $f = new Filter(['property' => 'user', 'value' => 'isNotNull']);

        $this->assertTrue($f->isValid());

        $f = new Filter(['property' => 'user', 'value' => 'in:1,2,5']);

        $this->assertTrue($f->isValid());

        $f = new Filter(['property' => 'user', 'value' => ['eq:1', 'eq:5']]);

        $this->assertTrue($f->isValid());
    }

    public function testHowWellItWorksWithGoodInput(): void
    {
        $f = new Filter(['property' => 'user.firstname', 'value' => 'eq:John']);

        $this->assertEquals('user.firstname', $f->getProperty());
        $this->assertEquals('John', $f->getValue());
        $this->assertEquals('eq', $f->getComparator());

        $this->assertSame($f, $f->setProperty('user.lastname'));
        $this->assertSame($f, $f->setValue('Doe%'));
        $this->assertSame($f, $f->setComparator('like'));

        $compiled = $f->compile();

        $this->assertTrue(\is_array($compiled));
        $this->assertArrayHasKey('property', $compiled);
        $this->assertArrayHasKey('value', $compiled);
        $this->assertEquals('user.lastname', $compiled['property']);
        $this->assertEquals('like:Doe%', $compiled['value']);

        // ---

        $f = new Filter(['property' => 'user', 'value' => 'isNull']);

        $this->assertEquals('user', $f->getProperty());
        $this->assertNull($f->getValue());
        $this->assertEquals('isNull', $f->getComparator());

        $compiled = $f->compile();

        $this->assertTrue(\is_array($compiled));
        $this->assertArrayHasKey('property', $compiled);
        $this->assertArrayHasKey('value', $compiled);
        $this->assertEquals('user', $compiled['property']);
        $this->assertEquals('isNull', $compiled['value']);

        // ---

        $f = new Filter(['property' => 'user', 'value' => 'in:1,5,8']);
        $inValue = $f->getValue();

        $this->assertTrue(\is_array($inValue));
        $this->assertSame(['1', '5', '8'], $inValue);

        // ---

        $f = new Filter(['property' => 'user', 'value' => 'in:2,4']);
        $notInValue = $f->getValue();

        $this->assertTrue(\is_array($notInValue));
        $this->assertSame(['2', '4'], $notInValue);

        // ---

        $f = new Filter(['property' => 'user', 'value' => 'in:']);

        $this->assertTrue(\is_array($f->getValue()));
        $this->assertSame([], $f->getValue());

        // ---

        $f = new Filter(['property' => 'user', 'value' => ['eq:5', 'gt:105']]);

        $this->assertTrue(\is_array($f->getValue()));
        $this->assertSame(
            [
                ['comparator' => 'eq', 'value' => '5'],
                ['comparator' => 'gt', 'value' => '105'],
            ],
            $f->getValue()
        );
    }

    public function testWithIsNullAndIsNotNullComparators(): void
    {
        $f = new Filter(['property' => 'user', 'value' => 'isNull']);

        $this->assertTrue($f->isValid());
        $this->assertEquals('user', $f->getProperty());
        $this->assertEquals('isNull', $f->getComparator());
        $this->assertNull($f->getValue());

        $f = new Filter(['property' => 'user', 'value' => 'isNotNull']);

        $this->assertTrue($f->isValid());
        $this->assertEquals('user', $f->getProperty());
        $this->assertEquals('isNotNull', $f->getComparator());
        $this->assertNull($f->getValue());
    }

    public function testGetSupportedComparators(): void
    {
        $result = Filter::getSupportedComparators();

        $this->assertTrue(\in_array('eq', $result));
        $this->assertTrue(\in_array('neq', $result));
    }

    public function testCreate(): void
    {
        $f = Filter::create('price', Filter::COMPARATOR_GREATER_THAN, 500);

        $this->assertInstanceOf(Filter::class, $f);
        $this->assertEquals('price', $f->getProperty());
        $this->assertEquals(Filter::COMPARATOR_GREATER_THAN, $f->getComparator());
        $this->assertEquals(500, $f->getValue());

        // ---

        $f = Filter::create('shippedAt', Filter::COMPARATOR_IS_NULL);
        $this->assertInstanceOf(Filter::class, $f);
        $this->assertEquals('shippedAt', $f->getProperty());
        $this->assertEquals(Filter::COMPARATOR_IS_NULL, $f->getComparator());
        $this->assertNull($f->getValue());
    }

    public static function compileDataProvider(): array
    {
        $result = [
            [
                'filter' => Filter::create('user.firstName', Filter::COMPARATOR_EQUAL, 'John'),
                'expected' => ['property' => 'user.firstName', 'value' => 'eq:John'],
            ],
            [
                'filter' => Filter::create('user.firstName', Filter::COMPARATOR_NOT_EQUAL, 'John'),
                'expected' => ['property' => 'user.firstName', 'value' => 'neq:John'],
            ],
            [
                'filter' => Filter::create('user.firstName', Filter::COMPARATOR_LIKE, 'Doe%'),
                'expected' => ['property' => 'user.firstName', 'value' => 'like:Doe%'],
            ],
            [
                'filter' => Filter::create('user.firstName', Filter::COMPARATOR_NOT_LIKE, 'Doe%'),
                'expected' => ['property' => 'user.firstName', 'value' => 'notLike:Doe%'],
            ],
            [
                'filter' => Filter::create('user.branch', Filter::COMPARATOR_IN, '1,2,3'),
                'expected' => ['property' => 'user.branch', 'value' => 'in:1,2,3'],
            ],
            [
                'filter' => Filter::create('user.branch', Filter::COMPARATOR_NOT_IN, '1,2,3'),
                'expected' => ['property' => 'user.branch', 'value' => 'notIn:1,2,3'],
            ],
            [
                'filter' => Filter::create('user.branch', Filter::COMPARATOR_IS_NULL),
                'expected' => ['property' => 'user.branch', 'value' => 'isNull'],
            ],
            [
                'filter' => Filter::create('user.branch', Filter::COMPARATOR_IS_NOT_NULL),
                'expected' => ['property' => 'user.branch', 'value' => 'isNotNull'],
            ],
            [
                'filter' => Filter::create('price', Filter::COMPARATOR_GREATER_THAN, '5'),
                'expected' => ['property' => 'price', 'value' => 'gt:5'],
            ],
            [
                'filter' => Filter::create('price', Filter::COMPARATOR_GREATER_THAN_OR_EQUAL, '5'),
                'expected' => ['property' => 'price', 'value' => 'gte:5'],
            ],
            [
                'filter' => Filter::create('price', Filter::COMPARATOR_LESS_THAN, '5'),
                'expected' => ['property' => 'price', 'value' => 'lt:5'],
            ],
            [
                'filter' => Filter::create('price', Filter::COMPARATOR_LESS_THAN_OR_EQUAL, '5'),
                'expected' => ['property' => 'price', 'value' => 'lte:5'],
            ],
        ];

        return $result;
    }

    /**
     * @dataProvider compileDataProvider
     */
    public function testCompile(Filter $filter, array $expected)
    {
        $this->assertSame($expected, $filter->compile());
    }
}
