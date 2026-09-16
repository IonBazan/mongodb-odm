<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Tests\Aggregation\Stage;

use Doctrine\ODM\MongoDB\Aggregation\Stage\Fill;
use Doctrine\ODM\MongoDB\Aggregation\Stage\SetWindowFields;
use Doctrine\ODM\MongoDB\Tests\Aggregation\AggregationTestTrait;
use Doctrine\ODM\MongoDB\Tests\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class SortOrderValidationTest extends BaseTestCase
{
    use AggregationTestTrait;

    #[DataProvider('provideInvalidSortStageOrders')]
    public function testSortStageRejectsInvalidOrder(mixed $order): void
    {
        $builder = $this->getTestAggregationBuilder();

        $this->expectException(InvalidArgumentException::class);

        $builder->sort('username', $order);
    }

    /** @return array<string, array{mixed}> */
    public static function provideInvalidSortStageOrders(): array
    {
        return [
            'non-integral float' => [1.9],
            'decimal string' => ['1.5'],
            'indexKey keyword' => ['indexKey'],
            'unknown $meta keyword' => [['$meta' => 'nonExistentScore']],
            'array without $meta' => [['bogus' => 'nonsense']],
            'empty array' => [[]],
        ];
    }

    public function testSortStageAcceptsRandValKeyword(): void
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->sort('username', 'randVal');

        self::assertSame([['$sort' => ['username' => ['$meta' => 'randVal']]]], $builder->getPipeline());
    }

    #[DataProvider('provideMetaSortOrders')]
    public function testFillSortByRejectsMetaSort(mixed $order): void
    {
        $fill = new Fill($this->getTestAggregationBuilder());

        $this->expectException(InvalidArgumentException::class);

        $fill->sortBy('username', $order);
    }

    #[DataProvider('provideMetaSortOrders')]
    public function testSetWindowFieldsSortByRejectsMetaSort(mixed $order): void
    {
        $setWindowFields = new SetWindowFields($this->getTestAggregationBuilder());

        $this->expectException(InvalidArgumentException::class);

        $setWindowFields->sortBy('username', $order);
    }

    /** @return array<string, array{mixed}> */
    public static function provideMetaSortOrders(): array
    {
        return [
            'meta keyword' => ['searchScore'],
            '$meta expression' => [['$meta' => 'searchScore']],
        ];
    }
}
