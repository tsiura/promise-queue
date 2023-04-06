# promise-queue
A PHP library for asynchronous, promise-based queues with execution concurrency limit.

## basic usage example
```
$q = new Queue();

$d1 = new Deferred();
$q->execute(function (int $jobId) use ($d1) {
    echo 'Run first' . PHP_EOL;
    return $d1->promise();
})->then(function ($value) {
    echo 'Resolve first: ' . $value . PHP_EOL;
});

$d2 = new Deferred();
$q->execute(function (int $jobId) use ($d2) {
    echo 'Run second' . PHP_EOL;
    return $d2->promise();
})->then(function ($value) {
    echo 'Resolve second: ' . $value . PHP_EOL;
});

$d3 = new Deferred();
$q->execute(function (int $jobId) use ($d3) {
    echo 'Run third' . PHP_EOL;
    return $d3->promise();
})->then(function ($value) {
    echo 'Resolve third: ' . $value . PHP_EOL;
});

$d1->resolve('value1');
$d2->resolve('value2');
$d3->resolve('value3');
```

### output
```
Run first
Resolve first: value1
Run second
Resolve second: value2
Run third
Resolve third: value3
```

### output will be the same even if we change sequence of resolving
```
$d1->resolve('value1');
$d3->resolve('value3');
$d2->resolve('value2');
```

### also you may return not promise from callable

```
$q->execute(function (int $jobId) use ($d2) {
    echo 'Runing job' . $jobId . PHP_EOL;
    return 1000;
})->then(function ($value) {
    echo 'Resolve job value: ' . $value . PHP_EOL;
});
```
### output will be
```
Runing job 1
Resolve job value: 1000
```