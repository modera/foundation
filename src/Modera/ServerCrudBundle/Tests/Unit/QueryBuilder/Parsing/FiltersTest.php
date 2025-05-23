<?php

namespace Modera\ServerCrudBundle\Tests\Unit\QueryBuilder\Parsing;

use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filter;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\Filters;
use Modera\ServerCrudBundle\QueryBuilder\Parsing\OrFilter;

class FiltersTest extends \PHPUnit\Framework\TestCase
{
    public function testHowWellItWorks(): void
    {
        $input = [
            ['property' => 'orderTotal', 'value' => 'gt:30'],
            ['property' => 'orderTotal', 'value' => 'lt:100'],
            ['property' => 'currency', 'value' => 'eq:2'],
            ['property' => 'paidAt', 'value' => 'isNotNull'],
            ['property' => 'shippedAt', 'value' => 'isNull'],
        ];

        $filters = new Filters($input);

        $orderTotalGt = $filters->findOneByPropertyAndComparator('orderTotal', Filter::COMPARATOR_GREATER_THAN);

        $this->assertInstanceOf(Filter::class, $orderTotalGt);
        $this->assertEquals('orderTotal', $orderTotalGt->getProperty());
        $this->assertEquals('gt', $orderTotalGt->getComparator());

        // ---

        $orderTotalFilters = $filters->findByProperty('orderTotal');

        $this->assertTrue(\is_array($orderTotalFilters));
        $this->assertEquals(2, \count($orderTotalFilters));
        $this->assertInstanceOf(Filter::class, $orderTotalFilters[0]);
        $this->assertInstanceOf(Filter::class, $orderTotalFilters[1]);
        $this->assertEquals(30, $orderTotalFilters[0]->getValue());
        $this->assertEquals(100, $orderTotalFilters[1]->getValue());

        $currencyFilter = $filters->findOneByProperty('currency');

        $this->assertInstanceOf(Filter::class, $currencyFilter);
        $this->assertEquals('eq', $currencyFilter->getComparator());
        $this->assertEquals(2, $currencyFilter->getValue());

        $thrownException = null;
        try {
            $filters->findOneByProperty('orderTotal');
        } catch (\RuntimeException $e) {
            $thrownException = $e;
        }
        $this->assertNotNull($thrownException);

        // ---

        $filters->removeFilter($orderTotalFilters[0]);

        $compiled = $filters->compile();

        $this->assertTrue(\is_array($compiled));
        $this->assertEquals(4, \count($compiled));

        $filters->addFilter(new Filter(['property' => 'address', 'value' => 'like:%Tallinn%']));

        $compiled = $filters->compile();

        $this->assertTrue(\is_array($compiled));
        $this->assertEquals(5, \count($compiled));

        // --- iterator

        $iteratedFilters = [];
        foreach ($filters as $filter) {
            $iteratedFilters[] = $filter;
        }

        $this->assertEquals(5, \count($iteratedFilters));
    }

    public function testHowWellItWorksWithMixedFilters(): void
    {
        $input = [
            ['property' => 'orderTotal', 'value' => ['eq:10', 'eq:20']],
            [
                ['property' => 'user.firstname', 'value' => 'like:Se%'],
                ['property' => 'user.lastname', 'value' => 'like:Li%'],
            ],
        ];

        $filters = new Filters($input);

        $this->assertEquals(2, \count($filters));
        $this->assertInstanceOf(Filter::class, $filters[0]);
        $this->assertInstanceOf(OrFilter::class, $filters[1]);
        $this->assertEquals('orderTotal', $filters[0]->getProperty());
        $this->assertNull($filters[0]->getComparator());
        $this->assertSame(
            [
                ['comparator' => 'eq', 'value' => '10'],
                ['comparator' => 'eq', 'value' => '20'],
            ],
            $filters[0]->getValue()
        );

        /** @var Filter[] $subFilters */
        $subFilters = $filters[1]->getFilters();

        $this->assertTrue(\is_array($subFilters));
        $this->assertEquals(2, \count($subFilters));
    }
}
