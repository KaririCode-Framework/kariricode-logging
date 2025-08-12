<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\Structural\HandlerAware;
use KaririCode\Contract\Logging\Structural\ProcessorAware;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\LoggerBuilder;
use KaririCode\Logging\LoggerManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * High-quality unit tests for the LoggerBuilder class.
 *
 * @category  Tests
 *
 * @author    Walmir Silva <walmir.silva@kariricode.org>
 * @license   MIT
 *
 * @see       https://kariricode.org/
 */
#[CoversClass(LoggerBuilder::class)]
#[UsesClass(LoggerManager::class)]
#[UsesClass(LineFormatter::class)]
final class LoggerBuilderTest extends TestCase
{
    private LoggerBuilder $builder;

    /**
     * Sets up the test environment before each test.
     */
    protected function setUp(): void
    {
        $this->builder = new LoggerBuilder('test_channel');
    }

    // ========================================================================
    // Build and Initial State Tests
    // ========================================================================

    /**
     * Tests that the build() method returns a LoggerManager instance
     * with the correct name and default formatter.
     */
    public function testBuildShouldReturnLoggerWithDefaultComponents(): void
    {
        // Act
        $logger = $this->builder->build();

        // Assert
        $this->assertInstanceOf(LoggerManager::class, $logger);
        $this->assertEquals('test_channel', $logger->getName());
        $this->assertInstanceOf(LineFormatter::class, $logger->getFormatter());
        $this->assertEmpty($logger->getHandlers(), 'The logger should have no handlers by default');
        $this->assertEmpty($logger->getProcessors(), 'The logger should have no processors by default');
    }

    /**
     * Tests that the builder can construct a logger with all custom components.
     */
    public function testBuildShouldCreateLoggerWithAllComponents(): void
    {
        // Arrange
        $mockHandler = $this->createMock(HandlerAware::class);
        $mockProcessor = $this->createMock(ProcessorAware::class);
        $mockFormatter = $this->createMock(LogFormatter::class);

        // Act
        $logger = $this->builder
            ->withHandler($mockHandler)
            ->withProcessor($mockProcessor)
            ->withFormatter($mockFormatter)
            ->build();

        // Assert
        $this->assertCount(1, $logger->getHandlers());
        $this->assertSame($mockHandler, $logger->getHandlers()[0]);

        $this->assertCount(1, $logger->getProcessors());
        $this->assertSame($mockProcessor, $logger->getProcessors()[0]);

        $this->assertSame($mockFormatter, $logger->getFormatter());
    }

    // ========================================================================
    // Fluent Interface (Method Chaining) Tests
    // ========================================================================

    /**
     * Tests that the withHandler() method adds the handler and returns the builder instance itself.
     */
    public function testWithHandlerShouldAddHandlerAndReturnSelf(): void
    {
        // Arrange
        $handler = $this->createMock(HandlerAware::class);

        // Act
        $result = $this->builder->withHandler($handler);

        // Assert
        $this->assertSame($this->builder, $result, 'The method should return its own instance for chaining.');

        // Verify the final product
        $logger = $result->build();
        $this->assertContains($handler, $logger->getHandlers());
    }

    /**
     * Tests that the withProcessor() method adds the processor and returns the builder instance itself.
     */
    public function testWithProcessorShouldAddProcessorAndReturnSelf(): void
    {
        // Arrange
        $processor = $this->createMock(ProcessorAware::class);

        // Act
        $result = $this->builder->withProcessor($processor);

        // Assert
        $this->assertSame($this->builder, $result, 'The method should return its own instance for chaining.');

        // Verify the final product
        $logger = $result->build();
        $this->assertContains($processor, $logger->getProcessors());
    }

    /**
     * Tests that the withFormatter() method sets the formatter and returns the builder instance itself.
     */
    public function testWithFormatterShouldSetFormatterAndReturnSelf(): void
    {
        // Arrange
        $formatter = $this->createMock(LogFormatter::class);

        // Act
        $result = $this->builder->withFormatter($formatter);

        // Assert
        $this->assertSame($this->builder, $result, 'The method should return its own instance for chaining.');

        // Verify the final product
        $logger = $result->build();
        $this->assertSame($formatter, $logger->getFormatter());
    }
}
