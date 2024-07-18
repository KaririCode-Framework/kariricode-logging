# KaririCode Framework: Logging Component

[![en](https://img.shields.io/badge/lang-en-red.svg)](README.md)
[![pt-br](https://img.shields.io/badge/lang-pt--br-green.svg)](README.pt-br.md)

![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Makefile](https://img.shields.io/badge/Makefile-1D1D1D?style=for-the-badge&logo=gnu&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PHPUnit](https://img.shields.io/badge/PHPUnit-78E130?style=for-the-badge&logo=phpunit&logoColor=white)

## Overview

The Logging Component is a crucial part of the KaririCode Framework, providing a robust, flexible, and PSR-3 compliant logging system. This component offers a comprehensive set of features for handling various logging needs, from simple application logging to complex, distributed system monitoring.

## Key Features

- **PSR-3 Compliance**: Fully implements the PSR-3 Logger Interface for seamless integration with other PSR-3 compatible systems.
- **Flexible Configuration**: Easily configurable through a `LoggerConfig` class, allowing for fine-tuned control over logging behavior.
- **Multiple Handlers**: Supports various output handlers, including stream and rotating file handlers.
- **Log Levels**: Implements all standard log levels as defined in PSR-3 (DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY).
- **Contextual Logging**: Supports adding context to log messages for more detailed debugging.
- **Processors**: Allows for the modification and enrichment of log records before they are written.
- **Formatters**: Customizable log message formatting.
- **Asynchronous Logging**: Optional asynchronous logging support for improved performance in high-load scenarios.
- **Log Rotation**: Built-in support for log file rotation to manage log file sizes.

## Available Interfaces and Classes

### Core Interfaces

- `Message`: Defines the contract for log messages.
- `Handler`: Defines the contract for log handlers.
- `Processor`: Defines the contract for log processors.
- `Formatter`: Defines the contract for log formatters.
- `Rotator`: Defines the contract for log file rotators.

### Main Classes

- `Logger`: The main logger class implementing PSR-3 LoggerInterface.
- `LoggerConfig`: Configuration class for the logger.
- `LogRecord`: Represents a single log record.
- `Level`: Enum class representing log levels.

### Handlers

- `StreamHandler`: Writes log records to any PHP stream (e.g., file, stderr).
- `RotatingFileHandler`: Writes log records to a file, with support for log rotation.

### Formatters

- `DefaultFormatter`: Provides a default formatting for log records.

### Processors

- `ContextProcessor`: Adds additional context (hostname, PID, memory usage) to log records.
- `ExceptionProcessor`: Formats exception information in log records.

### Rotators

- `DailyRotator`: Rotates log files on a daily basis.

## Installation

### Requirements

- PHP 8.3 or higher
- Composer

### Via Composer

```bash
composer require kariricode/logging
```

## Usage

### Basic Usage

Here's a basic example of how to use the Logging Component:

```php
use KaririCode\Logging\Logger;
use KaririCode\Logging\LoggerConfig;
use KaririCode\Logging\Level;
use KaririCode\Logging\Handler\RotatingFileHandler;
use KaririCode\Logging\Handler\StreamHandler;
use KaririCode\Logging\Processor\ContextProcessor;
use KaririCode\Logging\Processor\ExceptionProcessor;

$config = new LoggerConfig([
    'async' => true,
    'rotate' => true,
    'file_path' => '/var/log/myapp.log',
    'minimum_level' => 'info',
]);

$logger = new Logger('app', $config);
$logger->addHandler(new RotatingFileHandler('/var/log/application.log', 7, Level::DEBUG))
    ->addHandler(new StreamHandler('php://stderr', Level::ERROR))
    ->addProcessor(new ContextProcessor())
    ->addProcessor(new ExceptionProcessor());

$logger->info('Application started');
$logger->error('Critical error', ['exception' => new \Exception('Exception details')]);
```

### Dependency Injection and Advanced Usage

The Logging Component is designed to work seamlessly with dependency injection containers. Here's an example of how to use it with a simple DI container and in a more complex application structure:

```php
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use KaririCode\Logging\Logger;
use KaririCode\Logging\LoggerConfig;
use KaririCode\Logging\Handler\RotatingFileHandler;
use KaririCode\Logging\Level;

class Container implements ContainerInterface
{
    private array $services = [];

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new \Exception("Service not found: $id");
        }
        return $this->services[$id]($this);
    }

    public function has($id): bool
    {
        return isset($this->services[$id]);
    }

    public function set($id, callable $factory): void
    {
        $this->services[$id] = $factory;
    }
}

// Set up the container
$container = new Container();

// Configure the logger
$container->set(LoggerInterface::class, function (ContainerInterface $container) {
    $config = new LoggerConfig([
        'async' => true,
        'rotate' => true,
        'file_path' => '/var/log/myapp.log',
        'minimum_level' => 'info',
    ]);

    $logger = new Logger('app', $config);
    $logger->addHandler(new RotatingFileHandler('/var/log/application.log', 7, Level::DEBUG));
    return $logger;
});

// Example service that uses the logger
class UserService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function registerUser(string $username): void
    {
        // User registration logic here
        $this->logger->info('New user registered', ['username' => $username]);
    }
}

// Register the UserService in the container
$container->set(UserService::class, function (ContainerInterface $container) {
    return new UserService($container->get(LoggerInterface::class));
});

// Usage in application code
$userService = $container->get(UserService::class);
$userService->registerUser('john_doe');
```

In this example:

1. We define a simple `Container` class that implements the PSR-11 `ContainerInterface`.
2. We configure the logger in the container, making it available as a service.
3. We create a `UserService` class that depends on a `LoggerInterface`.
4. We register the `UserService` in the container, injecting the logger.
5. Finally, we retrieve the `UserService` from the container and use it in our application code.

This approach allows for better separation of concerns, easier testing, and more flexible configuration of your logging setup across your entire application.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support and Community

- **Documentation**: [https://kariricode.org](https://kariricode.org)
- **Issue Tracker**: [GitHub Issues](https://github.com/KaririCode-Framework/kariricode-logging/issues)
- **Community**: [KaririCode Club Community](https://kariricode.club)
- **Professional Support**: For enterprise-level support, contact us at support@kariricode.org

## Acknowledgments

- The KaririCode Framework team and contributors.
- The PHP community for their continued support and inspiration.

---

Built with ❤️ by the KaririCode team. Empowering developers to build more robust and flexible PHP applications.

Maintained by Walmir Silva - [walmir.silva@kariricode.org](mailto:walmir.silva@kariricode.org)
