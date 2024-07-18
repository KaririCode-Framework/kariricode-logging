<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Handler;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Handler\ExceptionHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerTest extends TestCase
{
    private ExceptionHandler $exceptionHandler;
    private Logger|MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $this->exceptionHandler = new ExceptionHandler($this->logger);
    }

    public function testHandle(): void
    {
        $exception = new \Exception('Test exception message');

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $exception->getMessage(),
                [
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]
            );

        $this->exceptionHandler->handle($exception);
    }

    public function testHandleWithDifferentException(): void
    {
        $exception = new \RuntimeException('Runtime exception message');

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $exception->getMessage(),
                [
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]
            );

        $this->exceptionHandler->handle($exception);
    }

    public function testHandleWithComplexException(): void
    {
        $previousException = new \Exception('Previous exception message');
        $exception = new \Exception('Complex exception message', 0, $previousException);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $exception->getMessage(),
                [
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]
            );

        $this->exceptionHandler->handle($exception);
    }
}
