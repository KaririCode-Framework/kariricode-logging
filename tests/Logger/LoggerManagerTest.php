<?php

declare(strict_types=1);

namespace KaririCode\Logging\Test\Logger;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\LogProcessor;
use KaririCode\Contract\Logging\Structural\HandlerAware;
use KaririCode\Contract\Logging\Structural\ProcessorAware;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Handler\AbstractHandler;
use KaririCode\Logging\LoggerManager;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\AbstractProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive unit tests for the LoggerManager class.
 *
 * @category  Tests
 *
 * @author    Walmir Silva <walmir.silva@kariricode.org>
 * @license   MIT
 *
 * @see      https://kariricode.org/
 */
#[CoversClass(LoggerManager::class)]
final class LoggerManagerTest extends TestCase
{
    private LoggerManager|MockObject $loggerManager;
    private HandlerAware|MockObject $mockHandler;
    private ProcessorAware|MockObject $mockProcessor;
    private LogFormatter|MockObject $mockFormatter;

    protected function setUp(): void
    {
        $this->mockHandler = $this->createMock(AbstractHandler::class);
        $this->mockProcessor = $this->createMock(AbstractProcessor::class);
        $this->mockFormatter = $this->createMock(LogFormatter::class);

        $this->loggerManager = new LoggerManager('test_logger');
    }

    // ========================================================================
    // Constructor and Initialization Tests
    // ========================================================================

    /**
     * Tests that LoggerManager can be instantiated with minimal parameters.
     */
    public function testCanBeInstantiatedWithMinimalParameters(): void
    {
        // Act
        $logger = new LoggerManager('test');

        // Assert
        $this->assertInstanceOf(LoggerManager::class, $logger);
        $this->assertSame('test', $logger->getName());
        $this->assertInstanceOf(LineFormatter::class, $logger->getFormatter());
        $this->assertEmpty($logger->getHandlers());
        $this->assertEmpty($logger->getProcessors());
    }

    /**
     * Tests that LoggerManager can be instantiated with all parameters.
     */
    public function testCanBeInstantiatedWithAllParameters(): void
    {
        // Arrange
        $handlers = [$this->mockHandler];
        $processors = [$this->mockProcessor];
        $formatter = $this->mockFormatter;

        // Act
        $logger = new LoggerManager('test', $handlers, $processors, $formatter);

        // Assert
        $this->assertSame('test', $logger->getName());
        $this->assertSame($handlers, $logger->getHandlers());
        $this->assertSame($processors, $logger->getProcessors());
        $this->assertSame($formatter, $logger->getFormatter());
    }

    /**
     * Tests that empty logger name is handled correctly.
     */
    public function testHandlesEmptyLoggerName(): void
    {
        // Act
        $logger = new LoggerManager('');

        // Assert
        $this->assertSame('', $logger->getName());
        $this->assertInstanceOf(LoggerManager::class, $logger);
    }

    // ========================================================================
    // Handler Management Tests
    // ========================================================================

    /**
     * Tests adding a single handler works correctly.
     */
    public function testAddSingleHandler(): void
    {
        // Act
        $result = $this->loggerManager->addHandler($this->mockHandler);

        // Assert
        $this->assertSame($this->loggerManager, $result, 'Should return self for method chaining');
        $this->assertCount(1, $this->loggerManager->getHandlers());
        $this->assertSame($this->mockHandler, $this->loggerManager->getHandlers()[0]);
    }

    /**
     * Tests adding multiple handlers maintains order.
     */
    public function testAddMultipleHandlersMaintainsOrder(): void
    {
        // Arrange
        /** @var HandlerAware */
        $handler2 = $this->createMock(HandlerAware::class);
        /** @var HandlerAware */
        $handler3 = $this->createMock(HandlerAware::class);

        // Act
        $this->loggerManager
            ->addHandler($this->mockHandler)
            ->addHandler($handler2)
            ->addHandler($handler3);

        // Assert
        $handlers = $this->loggerManager->getHandlers();
        $this->assertCount(3, $handlers);
        $this->assertSame($this->mockHandler, $handlers[0]);
        $this->assertSame($handler2, $handlers[1]);
        $this->assertSame($handler3, $handlers[2]);
    }

    // ========================================================================
    // Processor Management Tests
    // ========================================================================

    /**
     * Tests adding a single processor works correctly.
     */
    public function testAddSingleProcessor(): void
    {
        // Act
        $result = $this->loggerManager->addProcessor($this->mockProcessor);

        // Assert
        $this->assertSame($this->loggerManager, $result, 'Should return self for method chaining');
        $this->assertCount(1, $this->loggerManager->getProcessors());
        $this->assertSame($this->mockProcessor, $this->loggerManager->getProcessors()[0]);
    }

    /**
     * Tests adding multiple processors maintains order.
     */
    public function testAddMultipleProcessorsMaintainsOrder(): void
    {
        // Arrange
        /** @var ProcessorAware */
        $processor2 = $this->createMock(ProcessorAware::class);

        /** @var ProcessorAware */
        $processor3 = $this->createMock(ProcessorAware::class);

        // Act
        $this->loggerManager
            ->addProcessor($this->mockProcessor)
            ->addProcessor($processor2)
            ->addProcessor($processor3);

        // Assert
        $processors = $this->loggerManager->getProcessors();
        $this->assertCount(3, $processors);
        $this->assertSame($this->mockProcessor, $processors[0]);
        $this->assertSame($processor2, $processors[1]);
        $this->assertSame($processor3, $processors[2]);
    }

    // ========================================================================
    // Formatter Management Tests
    // ========================================================================

    /**
     * Tests setting a custom formatter.
     */
    public function testSetCustomFormatter(): void
    {
        // Act
        $result = $this->loggerManager->setFormatter($this->mockFormatter);

        // Assert
        $this->assertSame($this->loggerManager, $result, 'Should return self for method chaining');
        $this->assertSame($this->mockFormatter, $this->loggerManager->getFormatter());
    }

    /**
     * Tests that default formatter is LineFormatter.
     */
    public function testDefaultFormatterIsLineFormatter(): void
    {
        // Assert
        $this->assertInstanceOf(LineFormatter::class, $this->loggerManager->getFormatter());
    }

    // ========================================================================
    // Logging Operation Tests
    // ========================================================================

    /**
     * Tests logging with various levels calls handlers and processors correctly.
     *
     * @param LogLevel $level The log level to test
     * @param string $levelName The level name for display
     */
    #[DataProvider('logLevelProvider')]
    public function testLoggingWithVariousLevels(LogLevel $level, string $levelName): void
    {
        // Arrange
        $message = "Test {$levelName} message";
        $context = ['test' => 'context'];

        $this->mockProcessor->expects($this->once())
            ->method('process')
            ->with($this->callback(function (LogRecord $record) use ($level, $message, $context) {
                return $record->level === $level
                    && $record->message === $message
                    && $record->context === $context;
            }))
            ->willReturnCallback(fn (LogRecord $record) => $record);

        $this->mockHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(ImmutableValue::class));

        $this->loggerManager->addHandler($this->mockHandler);
        $this->loggerManager->addProcessor($this->mockProcessor);

        // Act
        $this->loggerManager->log($level, $message, $context);
    }

    /**
     * Tests logging without handlers doesn't cause errors.
     */
    public function testLoggingWithoutHandlersDoesNotCauseErrors(): void
    {
        // Act & Assert - Should not throw any exceptions
        $this->loggerManager->log(LogLevel::INFO, 'Test message');
        $this->expectNotToPerformAssertions();
    }

    /**
     * Tests logging without processors works correctly.
     */
    public function testLoggingWithoutProcessors(): void
    {
        // Arrange
        $this->mockHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(ImmutableValue::class));

        $this->loggerManager->addHandler($this->mockHandler);

        // Act
        $this->loggerManager->log(LogLevel::INFO, 'Test message');
    }

    /**
     * Tests that processors are called in the correct order.
     *
     * CORREÇÃO: Usar implementação concreta ao invés de mock para evitar
     * problemas com métodos final/static/não-existentes.
     */
    public function testProcessorsAreCalledInOrder(): void
    {
        // Arrange - Create concrete test processors instead of mocks
        $processor1 = new TestProcessor('processor1');
        $processor2 = new TestProcessor('processor2');

        $this->loggerManager
            ->addProcessor($processor1)
            ->addProcessor($processor2);

        // Act
        $this->loggerManager->log(LogLevel::INFO, 'Test message');

        // Assert - Check that processors were called in correct order
        $callOrder = TestProcessor::getCallOrder();
        $this->assertSame(['processor1', 'processor2'], $callOrder);

        // Reset for other tests
        TestProcessor::resetCallOrder();
    }

    // ========================================================================
    // Threshold Management Tests
    // ========================================================================

    /**
     * Tests setting and using thresholds for filtering.
     */
    public function testThresholdFiltering(): void
    {
        // Arrange
        $this->mockHandler->expects($this->never())->method('handle');
        $this->loggerManager->addHandler($this->mockHandler);
        $this->loggerManager->setThreshold('execution_time', 1000);

        // Act - Log with context below threshold
        $this->loggerManager->log(LogLevel::INFO, 'Test message', ['execution_time' => 500]);

        // Assert - Handler should not be called due to threshold filtering
    }

    /**
     * Tests that messages above threshold are processed.
     */
    public function testMessagesAboveThresholdAreProcessed(): void
    {
        // Arrange
        $this->mockHandler->expects($this->once())->method('handle');
        $this->loggerManager->addHandler($this->mockHandler);
        $this->loggerManager->setThreshold('execution_time', 1000);

        // Act - Log with context above threshold
        $this->loggerManager->log(LogLevel::INFO, 'Test message', ['execution_time' => 1500]);
    }

    /**
     * Tests multiple thresholds work correctly.
     */
    public function testMultipleThresholds(): void
    {
        // Arrange
        $this->mockHandler->expects($this->never())->method('handle');
        $this->loggerManager->addHandler($this->mockHandler);
        $this->loggerManager->setThreshold('execution_time', 1000);
        $this->loggerManager->setThreshold('memory_usage', 100);

        // Act - One threshold met, one not
        $this->loggerManager->log(LogLevel::INFO, 'Test message', [
            'execution_time' => 1500, // Above threshold
            'memory_usage' => 50,      // Below threshold
        ]);

        // Assert - Should be filtered out because memory_usage is below threshold
    }

    // ========================================================================
    // Edge Cases and Error Conditions
    // ========================================================================

    /**
     * Tests logging with stringable message objects.
     *
     * CORREÇÃO: Ajustar expectativa para verificar que o LogRecord contém
     * o objeto Stringable correto, não a string convertida.
     */
    public function testLoggingWithStringableMessage(): void
    {
        // Arrange
        $stringableMessage = new class implements \Stringable {
            public function __toString(): string
            {
                return 'Stringable message';
            }
        };

        $this->mockHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (LogRecord $record) use ($stringableMessage) {
                // Verify that the record contains the original Stringable object
                return $record->message === $stringableMessage
                    && 'Stringable message' === $record->getMessageAsString();
            }));

        $this->loggerManager->addHandler($this->mockHandler);

        // Act
        $this->loggerManager->log(LogLevel::INFO, $stringableMessage);
    }

    /**
     * Tests logging with null context values.
     */
    public function testLoggingWithNullContextValues(): void
    {
        // Arrange
        $context = ['key' => null, 'other' => 'value'];

        $this->mockHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (LogRecord $record) use ($context) {
                return $record->context === $context;
            }));

        $this->loggerManager->addHandler($this->mockHandler);

        // Act
        $this->loggerManager->log(LogLevel::INFO, 'Test message', $context);
    }

    /**
     * Tests logging with extremely large context arrays.
     */
    public function testLoggingWithLargeContextArrays(): void
    {
        // Arrange
        $largeContext = [];
        for ($i = 0; $i < 1000; ++$i) {
            $largeContext["key_{$i}"] = "value_{$i}";
        }

        $this->mockHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(ImmutableValue::class));

        $this->loggerManager->addHandler($this->mockHandler);

        // Act & Assert - Should handle large context without issues
        $this->loggerManager->log(LogLevel::INFO, 'Test message', $largeContext);
    }

    /**
     * Tests logging with circular references in context.
     */
    public function testLoggingWithCircularReferencesInContext(): void
    {
        // Arrange
        $obj1 = new \stdClass();
        $obj2 = new \stdClass();
        $obj1->ref = $obj2;
        $obj2->ref = $obj1; // Circular reference

        $context = ['circular' => $obj1];

        $this->mockHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(ImmutableValue::class));

        $this->loggerManager->addHandler($this->mockHandler);

        // Act & Assert - Should handle circular references gracefully
        $this->loggerManager->log(LogLevel::INFO, 'Test message', $context);
    }

    // ========================================================================
    // Performance Tests
    // ========================================================================

    /**
     * Tests logging performance under high load.
     */
    public function testLoggingPerformanceUnderHighLoad(): void
    {
        // Arrange
        $this->loggerManager->addHandler($this->mockHandler);
        $startTime = microtime(true);

        // Act
        for ($i = 0; $i < 1000; ++$i) {
            $this->loggerManager->log(LogLevel::INFO, "Message {$i}", ['iteration' => $i]);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Assert
        $this->assertLessThan(1.0, $executionTime, 'Should log 1000 messages in under 1 second');
    }

    /**
     * Tests memory usage remains stable during logging.
     */
    public function testMemoryUsageStability(): void
    {
        // Arrange
        $this->loggerManager->addHandler($this->mockHandler);
        $initialMemory = memory_get_usage();

        // Act
        for ($i = 0; $i < 100; ++$i) {
            $this->loggerManager->log(LogLevel::INFO, "Message {$i}");
        }

        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;

        // Assert
        $this->assertLessThan(1024 * 1024, $memoryIncrease, 'Memory increase should be less than 1MB');
    }

    // ========================================================================
    // Integration-Style Tests
    // ========================================================================

    /**
     * Tests a realistic logging workflow with multiple operations.
     */
    public function testRealisticLoggingWorkflow(): void
    {
        // Arrange
        /** @var AbstractHandler&MockObject */
        $handler = $this->createMock(AbstractHandler::class);
        /** @var AbstractProcessor&MockObject */
        $processor = $this->createMock(AbstractProcessor::class);

        // Expect multiple calls in sequence
        $handler->expects($this->exactly(5))->method('handle');
        $processor->expects($this->exactly(5))
            ->method('process')
            ->willReturnCallback(fn ($record) => $record);

        $this->loggerManager
            ->addHandler($handler)
            ->addProcessor($processor)
            ->setThreshold('response_time', 100);

        // Act - Simulate real application logging
        $this->loggerManager->info('Application started');
        $this->loggerManager->debug('Processing request', ['user_id' => 123]);
        $this->loggerManager->warning('Slow query detected', ['response_time' => 150]);
        $this->loggerManager->error('Database connection failed', ['attempts' => 3]);
        $this->loggerManager->critical('System overload detected', ['cpu' => 95]);
    }

    // ========================================================================
    // Data Providers
    // ========================================================================

    /**
     * Provides log levels for testing.
     *
     * @return array<string, array<LogLevel|string>>
     */
    public static function logLevelProvider(): array
    {
        return [
            'emergency level' => [LogLevel::EMERGENCY, 'emergency'],
            'alert level' => [LogLevel::ALERT, 'alert'],
            'critical level' => [LogLevel::CRITICAL, 'critical'],
            'error level' => [LogLevel::ERROR, 'error'],
            'warning level' => [LogLevel::WARNING, 'warning'],
            'notice level' => [LogLevel::NOTICE, 'notice'],
            'info level' => [LogLevel::INFO, 'info'],
            'debug level' => [LogLevel::DEBUG, 'debug'],
        ];
    }
}

// ========================================================================
// Test Helper Classes
// ========================================================================

/**
 * Concrete test processor for testing processor order.
 *
 * Usado para testar a ordem de processamento sem problemas de mock.
 */
class TestProcessor implements ProcessorAware
{
    private static array $callOrder = [];

    public function __construct(private string $name)
    {
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        self::$callOrder[] = $this->name;

        return $record;
    }

    public function addProcessor(LogProcessor $processor): ProcessorAware
    {
        // Not implemented for this test
        return $this;
    }

    public function getProcessors(): array
    {
        return [];
    }

    public static function getCallOrder(): array
    {
        return self::$callOrder;
    }

    public static function resetCallOrder(): void
    {
        self::$callOrder = [];
    }
}
