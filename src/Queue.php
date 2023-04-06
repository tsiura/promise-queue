<?php

declare(strict_types=1);

namespace Tsiura\PromiseQueue;

use SplQueue;
use Closure;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

class Queue
{
    use LoggerAwareTrait;

    private const MAX_ITEM_ID = 65000;

    private SplQueue $queue;
    private int $id = 1;
    private int $pending = 0;

    /**
     * @param int $maxPendingPromises Max promises executed at same time, default 1
     * @param int $maxQueuedPromises Max promises awaited execution, default 0 (unlimited)
     */
    public function __construct(
        private readonly int $maxPendingPromises = 1,
        private readonly int $maxQueuedPromises = 0,
    ) {
        $this->logger = new NullLogger();
        $this->queue = new SplQueue();
    }

    public function clear(): void
    {
        $this->queue = new SplQueue();
    }

    public function count(): int
    {
        return $this->queue->count();
    }

    public function execute(Closure $callback): Promise
    {
        return new Promise(fn ($resolve, $reject) => $this->enqueue($resolve, $reject, $callback));
    }

    private function enqueue(Closure $resolve, Closure $reject, Closure $callback): void
    {
        if ($this->maxQueuedPromises > 0 && $this->queue->count() >= $this->maxQueuedPromises) {
            $reject(new QueueException('Max number of queued promises exceeded'));
            return;
        }

        $id = $this->getNextId();
        $this->queue->push(new QueueItem($id, $callback, $resolve, $reject));
        $this->logger->debug(sprintf('Enqueued new job with id %d. Pending - %d, queued - %d', $id, $this->pending, $this->count()));
        $this->executeNext();
    }

    private function executeNext(): void
    {
        $job = $this->dequeue();
        if (null === $job) {
            return;
        }

        $this->pending++;

        $this->logger->debug(sprintf('Executing job %d. Pending - %d, queued - %d', $job->id, $this->pending, $this->count()));

        try {
            $promise = call_user_func($job->callback, $job->id);
            if (!($promise instanceof PromiseInterface)) {
                $promise = resolve($promise);
            }
            $promise->then(
                function ($result) use ($job) {
                    $this->pending--;
                    $this->logger->debug(sprintf('Resolving job %d. Pending - %d, queued - %d', $job->id, $this->pending, $this->count()));
                    call_user_func($job->resolve, $result);
                    $this->logger->debug(sprintf('Resolved job %d', $job->id));
                    $this->executeNext();
                },
                function ($error) use ($job) {
                    $this->pending--;
                    $this->logger->debug(sprintf('Rejecting job %d. Pending - %d, queued - %d', $job->id, $this->pending, $this->count()));
                    call_user_func($job->reject, $error);
                    $this->logger->debug(sprintf('Rejected job %d', $job->id));
                    $this->executeNext();
                }
            );
        } catch (\Throwable $e) {
            $this->pending--;
            $this->logger->debug(sprintf('Error in job %d - %s', $job->id, $e->getMessage()));
            call_user_func($job->reject, $e);
            $this->logger->debug(sprintf('Rejected job %d', $job->id));
            $this->executeNext();
        }
    }

    private function dequeue(): ?QueueItem
    {
        if ($this->queue->isEmpty()) {
            return null;
        }

        if ($this->pending >= $this->maxPendingPromises) {
            return null;
        }

        return $this->queue->dequeue();
    }

    private function getNextId(): int
    {
        if ($this->id >= self::MAX_ITEM_ID) {
            $this->id = 1;
        }

        return $this->id++;
    }
}
