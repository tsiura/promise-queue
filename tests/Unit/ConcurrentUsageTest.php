<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use React\Promise\Deferred;
use React\Promise\Promise;
use Tsiura\PromiseQueue\Queue;
use Tsiura\PromiseQueue\QueueException;

class ConcurrentUsageTest extends TestCase
{
    public function testSequenceBasic()
    {
        $test = [];

        $q = new Queue(2);

        $d1 = new Deferred();
        $q->execute(function () use ($d1) {
            return $d1->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d2 = new Deferred();
        $q->execute(function () use ($d2) {
            return $d2->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d3 = new Deferred();
        $q->execute(function () use ($d3) {
            return $d3->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        // 2 - running, 1 - enqueued
        self::assertEquals(1, $q->count());

        $d1->resolve(10);
        $d2->resolve(30);
        $d3->resolve(20);

        self::assertEquals([10, 30, 20], $test);
    }

    public function testLimit1()
    {
        $test = [];
        $e = '';

        $q = new Queue(1, 1);

        $d1 = new Deferred();
        $q->execute(function () use ($d1) {
            return $d1->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d2 = new Deferred();
        $q->execute(function () use ($d2) {
            return $d2->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d3 = new Deferred();
        /** @var Promise $promise */
        $promise = $q->execute(function () use ($d3) {
            return $d3->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $promise->otherwise(function (QueueException $ex) use (&$e) {
            $e = $ex->getMessage();
        });

        self::assertEquals(1, $q->count());
        self::assertEquals('Max number of queued promises exceeded', $e);
    }

    public function testLimit2()
    {
        $test = [];
        $e = '';

        $q = new Queue(1, 2);

        $d1 = new Deferred();
        $q->execute(function () use ($d1) {
            return $d1->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d2 = new Deferred();
        $q->execute(function () use ($d2) {
            return $d2->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d3 = new Deferred();
        /** @var Promise $promise */
        $promise = $q->execute(function () use ($d3) {
            return $d3->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });
        $promise->otherwise(function (QueueException $ex) use (&$e) {
            $e = $ex->getMessage();
        });

        self::assertEquals(2, $q->count());
        self::assertEmpty($e);
    }
}
