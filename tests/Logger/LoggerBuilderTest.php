<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Contract\Logging\Structural\HandlerAware;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\LoggerBuilder;
use PHPUnit\Framework\TestCase;

class LoggerBuilderTest extends TestCase
{
    public function testBuildLogger(): void
    {
        $builder = new LoggerBuilder('test');
        $logger = $builder->build();

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('test', $logger->getName());
    }

    public function testWithHandler(): void
    {
        $handler = $this->createMock(HandlerAware::class);
        $builder = new LoggerBuilder('test');
        $builder->withHandler($handler);

        $logger = $builder->build();

        $this->assertContains($handler, $logger->getHandlers());
    }

    public function testWithProcessor(): void
    {
        $processor = $this->createMock(\KaririCode\Contract\Logging\ProcessorAware::class);
        $builder = new LoggerBuilder('test');
        $builder->withProcessor($processor);

        $logger = $builder->build();

        $this->assertContains($processor, $logger->getProcessors());
    }

    public function testWithFormatter(): void
    {
        $formatter = new LineFormatter();
        $builder = new LoggerBuilder('test');
        $builder->withFormatter($formatter);

        $logger = $builder->build();

        $this->assertSame($formatter, $logger->getFormatter());
    }
}
