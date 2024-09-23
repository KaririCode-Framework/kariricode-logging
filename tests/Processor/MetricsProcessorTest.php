<?php

declare(strict_types=1);

use KaririCode\Contract\Logging\LogProcessor;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\MetricsProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetricsProcessorTest extends TestCase
{
    public function testProcessWithMultipleProcessors(): void
    {
        $record = new LogRecord(
            LogLevel::INFO,
            'Initial message',
            [],
            new DateTimeImmutable()
        );

        /** @var MockObject&LogProcessor $processor1 */
        $processor1 = $this->createMock(LogProcessor::class);
        $processor1->expects($this->once())
            ->method('process')
            ->with($record)
            ->willReturn($record);

        /** @var MockObject&LogProcessor $processor2 */
        $processor2 = $this->createMock(LogProcessor::class);
        $processor2->expects($this->once())
            ->method('process')
            ->with($record)
            ->willReturn($record);

        $processors = [$processor1, $processor2];
        $metricsProcessor = new MetricsProcessor($processors);

        $result = $metricsProcessor->process($record);

        $this->assertSame($record, $result);
    }

    public function testProcessWithoutProcessors(): void
    {
        $record = new LogRecord(
            LogLevel::INFO,
            'Initial message',
            [],
            new DateTimeImmutable()
        );

        $metricsProcessor = new MetricsProcessor([]);

        $result = $metricsProcessor->process($record);

        $this->assertSame($record, $result);
    }

    public function testProcessWithProcessorThrowingException(): void
    {
        $record = new LogRecord(
            LogLevel::INFO,
            'Initial message',
            [],
            new DateTimeImmutable()
        );

        /** @var MockObject&LogProcessor $processor */
        $processor = $this->createMock(LogProcessor::class);
        $processor->expects($this->once())
            ->method('process')
            ->with($record)
            ->willThrowException(new RuntimeException('Processor failed'));

        $processors = [$processor];
        $metricsProcessor = new MetricsProcessor($processors);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Processor failed');

        $metricsProcessor->process($record);
    }
}
