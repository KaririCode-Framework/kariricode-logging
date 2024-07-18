<?php

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;

class QueryLogger
{
    private Logger $logger;
    private int $threshold;

    public function __construct(Logger $logger, int $threshold)
    {
        $this->logger = $logger;
        $this->threshold = $threshold;
    }

    public function log(string $query, array $bindings, float $time): void
    {
        if ($time >= $this->threshold) {
            $this->logger->warning('Slow query detected', [
                'query' => $query,
                'bindings' => $bindings,
                'time' => $time,
            ]);
        } else {
            $this->logger->debug('Query executed', [
                'query' => $query,
                'bindings' => $bindings,
                'time' => $time,
            ]);
        }
    }
}
