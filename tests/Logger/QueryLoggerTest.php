<?php

namespace KaririCode\Logging\Tests;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\QueryLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class QueryLoggerTest extends TestCase
{
    private QueryLogger $queryLogger;
    private Logger|MockObject $logger;

    protected function setUp(): void
    {
        /** @var Logger */
        $this->logger = $this->createMock(Logger::class);
        $this->queryLogger = new QueryLogger($this->logger, 100);
    }

    public function testLogSlowQuery(): void
    {
        $query = 'SELECT * FROM users WHERE id = ?';
        $bindings = [1];
        $executionTime = 150.0;

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                'Slow query detected',
                [
                    'query' => $query,
                    'bindings' => $bindings,
                    'time' => $executionTime,
                ]
            );

        $this->queryLogger->log($query, $bindings, $executionTime);
    }

    public function testLogFastQuery(): void
    {
        $query = 'SELECT * FROM users WHERE id = ?';
        $bindings = [1];
        $executionTime = 50.0;

        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                'Query executed',
                [
                    'query' => $query,
                    'bindings' => $bindings,
                    'time' => $executionTime,
                ]
            );

        $this->queryLogger->log($query, $bindings, $executionTime);
    }
}
