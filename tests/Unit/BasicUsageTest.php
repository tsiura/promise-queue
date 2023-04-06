<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use React\Promise\Deferred;
use Tsiura\PromiseQueue\Queue;

class BasicUsageTest extends TestCase
{
    public function testSequenceBasic()
    {
        $test = [];

        $q = new Queue();

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

        $d1->resolve(10);
        $d2->resolve(20);
        $d3->resolve(30);

        self::assertEquals([10, 20, 30], $test);
    }

    public function testSequenceShuffle()
    {
        $test = [];

        $q = new Queue();

        $d1 = new Deferred();
        $q->execute(function (int $id) use ($d1) {
            self::assertEquals(1, $id);
            return $d1->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d2 = new Deferred();
        $q->execute(function (int $id) use ($d2) {
            self::assertEquals(2, $id);
            return $d2->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d3 = new Deferred();
        $q->execute(function (int $id) use ($d3) {
            self::assertEquals(3, $id);
            return $d3->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        self::assertEquals(2, $q->count());

        $d1->resolve(10);
        self::assertEquals(1, $q->count());

        $d3->resolve(30);
        self::assertEquals(1, $q->count());

        $d2->resolve(20);

        self::assertEquals([10, 20, 30], $test);
    }

    public function testReturnNotPromise()
    {
        $test = [];

        $q = new Queue();

        $q->execute(function () {
            return 10;
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $q->execute(function () {
            return 20;
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d3 = new Deferred();
        $q->execute(function () use ($d3) {
            return $d3->promise();
        })->then(function ($value) use (&$test) {
            $test[] = $value;
        });

        $d3->resolve(30);

        self::assertEquals([10, 20, 30], $test);
    }
}
