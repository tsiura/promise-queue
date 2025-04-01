<?php

declare(strict_types=1);

namespace Zeran\PromiseQueue;

use Closure;

final readonly class QueueItem
{
    public function __construct(
        public int $id,
        public Closure $callback,
        public Closure $resolve,
        public Closure $reject,
    ) {
    }
}
