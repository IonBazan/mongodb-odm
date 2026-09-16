<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Tests\Query;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Tests\BaseTestCase;
use Documents\User;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class BuilderSortOrderTest extends BaseTestCase
{
    #[DataProvider('provideInvalidSortOrders')]
    public function testSortRejectsInvalidOrder(mixed $order): void
    {
        $qb = new Builder($this->dm, User::class);

        $this->expectException(InvalidArgumentException::class);

        $qb->sort('username', $order);
    }

    /** @return array<string, array{mixed}> */
    public static function provideInvalidSortOrders(): array
    {
        return [
            'string with numeric prefix' => ['1abc'],
            'non-integral float' => [1.9],
            'negative non-integral float' => [-1.4],
            'decimal string' => ['1.5'],
            'indexKey keyword' => ['indexKey'],
            'unknown $meta keyword' => [['$meta' => 'nonExistentScore']],
            'array without $meta' => [['bogus' => 'nonsense']],
            'empty array' => [[]],
            'nested $meta' => [['$meta' => ['$meta' => 'textScore']]],
        ];
    }
}
