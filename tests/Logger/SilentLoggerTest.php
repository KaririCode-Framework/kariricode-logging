<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\SilentLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SilentLogger::class)]
final class SilentLoggerTest extends TestCase
{
    /**
     * Tests that the logger can be instantiated with its default name.
     */
    public function testShouldBeInstantiableWithDefaultName(): void
    {
        // Arrange
        $logger = new SilentLogger();

        // Assert
        $this->assertInstanceOf(Logger::class, $logger, 'SilentLogger must implement the Logger interface.');
        $this->assertEquals('silent', $logger->getName(), 'The default name for the logger should be "silent".');
    }

    /**
     * Tests that the logger can be instantiated with a custom name.
     */
    public function testShouldBeInstantiableWithCustomName(): void
    {
        // Arrange
        $logger = new SilentLogger('custom_channel');

        // Assert
        $this->assertEquals('custom_channel', $logger->getName(), 'The logger should accept and return a custom name.');
    }

    /**
     * Tests that calling the main log() method does not produce any errors or output.
     * This is the core behavior of the Null Object Pattern.
     */
    public function testLogMethodShouldDoNothingAndNotThrowErrors(): void
    {
        // Arrange
        $logger = new SilentLogger();
        $this->expectNotToPerformAssertions(); // The assertion is that no error/exception occurs.

        // Act
        $logger->log(LogLevel::INFO, 'This message should be discarded silently.');
    }

    /**
     * Tests that convenience methods like info(), error(), etc., can be called safely.
     * These methods are provided by the LoggerTrait and rely on the empty log() method.
     */
    public function testConvenienceMethodsShouldDoNothingAndNotThrowErrors(): void
    {
        // Arrange
        $logger = new SilentLogger();
        $this->expectNotToPerformAssertions();

        // Act
        $logger->info('Info message to be discarded.');
        $logger->error('Error message to be discarded.', ['exception' => 'details']);
        $logger->warning('Warning message to be discarded.');
    }
}
