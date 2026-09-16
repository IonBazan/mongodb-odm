<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Tests\Functional;

use Doctrine\ODM\MongoDB\Tests\BaseTestCase;
use Documents\User;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A sort order must either be rejected by the builder or produce a sort the
 * server can execute, never a query that only fails on the server.
 */
class SortOrderServerTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = new User();
        $user->setUsername('alice');
        $this->dm->persist($user);
        $this->dm->flush();
    }

    #[DataProvider('provideSortOrders')]
    public function testQueryBuilderSortIsRejectedOrExecutable(mixed $order): void
    {
        try {
            $qb = $this->dm->createQueryBuilder(User::class)->sort('username', $order);
        } catch (InvalidArgumentException) {
            $this->addToAssertionCount(1);

            return;
        }

        self::assertCount(1, $qb->getQuery()->toArray());
    }

    #[DataProvider('provideSortOrders')]
    public function testAggregationBuilderSortIsRejectedOrExecutable(mixed $order): void
    {
        try {
            $stage = $this->dm->createAggregationBuilder(User::class)->sort('username', $order);
        } catch (InvalidArgumentException) {
            $this->addToAssertionCount(1);

            return;
        }

        self::assertCount(1, $stage->getAggregation()->execute()->toArray());
    }

    /** @return array<string, array{mixed}> */
    public static function provideSortOrders(): array
    {
        return [
            'indexKey keyword' => ['indexKey'],
            'indexKey $meta expression' => [['$meta' => 'indexKey']],
            'unknown $meta keyword' => [['$meta' => 'nonExistentScore']],
            'array without $meta' => [['bogus' => 'nonsense']],
        ];
    }
}
