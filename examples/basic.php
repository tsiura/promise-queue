<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use React\Promise\Deferred;
use Tsiura\PromiseQueue\Queue;

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

class MyLogger implements LoggerInterface
{
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement emergency() method.
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement alert() method.
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement critical() method.
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement error() method.
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement warning() method.
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement notice() method.
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        // TODO: Implement info() method.
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        echo sprintf('%s', $message) . PHP_EOL;
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        // TODO: Implement log() method.
    }
}

$q = new Queue();
$q->setLogger(new MyLogger());

$d1 = new Deferred();
$q->execute(function (int $jobId) use ($d1) {
    echo $jobId . ' - Run first' . PHP_EOL;
    return $d1->promise();
})->then(function ($value) {
    echo 'Resolve first: ' . $value . PHP_EOL;
});

$d2 = new Deferred();
$q->execute(function (int $jobId) use ($d2) {
    echo $jobId . ' - Run second' . PHP_EOL;
    return $d2->promise();
})->then(function ($value) {
    echo 'Resolve second: ' . $value . PHP_EOL;
});

$d3 = new Deferred();
$q->execute(function (int $jobId) use ($d3) {
    echo $jobId . ' - Run third' . PHP_EOL;
    return $d3->promise();
})->then(function ($value) {
    echo 'Resolve third: ' . $value . PHP_EOL;
})->otherwise(function (\Throwable $e) { echo $e->getMessage() . PHP_EOL; });

$d4 = new Deferred();
$q->execute(function (int $jobId) use ($d4) {
    echo $jobId . ' - Run fourth' . PHP_EOL;
    return $d4->promise();
})->then(function ($value) {
    echo 'Resolve fourth: ' . $value . PHP_EOL;
})->otherwise(function (\Throwable $e) { echo $e->getMessage() . PHP_EOL; });

$d1->resolve('value1');
$d2->resolve('value2');
$d3->resolve('value3');
$d4->resolve('value4');