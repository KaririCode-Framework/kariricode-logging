# KaririCode Framework: Logging Component

[![en](https://img.shields.io/badge/lang-en-red.svg)](README.md)
[![pt-br](https://img.shields.io/badge/lang-pt--br-green.svg)](README.pt-br.md)

![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Makefile](https://img.shields.io/badge/Makefile-1D1D1D?style=for-the-badge&logo=gnu&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PHPUnit](https://img.shields.io/badge/PHPUnit-78E130?style=for-the-badge&logo=phpunit&logoColor=white)

A robust, flexible, and PSR-3 compliant logging component for the KaririCode Framework, providing comprehensive logging capabilities for PHP applications.

## Features

- PSR-3 compliant
- Supports multiple log channels (file, Slack, Papertrail, Elasticsearch)
- Log encryption
- Asynchronous logging support
- Query and performance logging
- Flexible logging formatters
- Supports log rotation and cleanup
- Circuit breaker and retry logic for logging
- Detailed context and structured logging

## Installation

To install the KaririCode Logging component, run the following command:

```bash
composer require kariricode/logging
```

## Basic Usage

### Step 1: Environment Configuration

The **KaririCode Logging Component** relies on several environment variables to configure logging channels, log levels, external services, and other parameters. These variables are defined in a `.env` file, and the project comes with a default `.env.example` that should be copied to `.env` for initial setup.

To copy and create your `.env` file, run the following command:

```bash
make setup-env
```

This command will create a `.env` file if it doesn't already exist. Afterward, you can modify the values according to your requirements. Below are some key variables and their descriptions:

```ini
# Application environment (e.g., production, develop)
KARIRICODE_APP=develop

# PHP version and port used by the Docker service
KARIRICODE_PHP_VERSION=8.3
KARIRICODE_PHP_PORT=9303

# Default log channel (e.g., file, stderr, slack)
LOG_CHANNEL=file

# Log level (e.g., debug, info, warning, error)
LOG_LEVEL=debug

# Encryption key for log data (ensure this is kept secure)
LOG_ENCRYPTION_KEY=83302e6472acda6a8aeadf78409ceda3959994991393cdafbe23d2a46a148ba4

# Slack configuration for sending critical logs
SLACK_BOT_TOKEN=xoxb-your-bot-token-here
SLACK_CHANNEL=#your-channel-name

# Papertrail logging service configuration
PAPERTRAIL_URL=logs.papertrailapp.com
PAPERTRAIL_PORT=12345

# Formatter for logs written to stderr
LOG_STDERR_FORMATTER=json

# Elasticsearch index for storing logs
ELASTIC_LOG_INDEX=logging-logs

# Enable or disable asynchronous logging
ASYNC_LOG_ENABLED=true

# Enable or disable query logging, and configure thresholds
QUERY_LOG_ENABLED=true
QUERY_LOG_CHANNEL=file
QUERY_LOG_THRESHOLD=100

# Enable or disable performance logging, and configure thresholds
PERFORMANCE_LOG_ENABLED=true
PERFORMANCE_LOG_CHANNEL=file
PERFORMANCE_LOG_THRESHOLD=1000

# Enable or disable error logging
ERROR_LOG_ENABLED=true
ERROR_LOG_CHANNEL=file

# Log cleanup configuration (automatic removal of logs older than the specified number of days)
LOG_CLEANER_ENABLED=true
LOG_CLEANER_KEEP_DAYS=30

# Circuit breaker configuration for managing log retries
CIRCUIT_BREAKER_FAILURE_THRESHOLD=3
CIRCUIT_BREAKER_RESET_TIMEOUT=60

# Retry configuration for log failures
RETRY_MAX_ATTEMPTS=3
RETRY_DELAY=1000
RETRY_MULTIPLIER=2
RETRY_JITTER=100
```

Each of these variables can be adjusted according to your specific needs:

- **Log Channels:** You can choose between different logging channels such as `file`, `slack`, or `stderr`. For example, `LOG_CHANNEL=slack` will send critical logs to a Slack channel.
- **Log Levels:** This defines the minimum severity level for logs to be recorded (e.g., `debug`, `info`, `warning`, `error`, `critical`).
- **External Services:** If you want to send logs to external services like Slack or Papertrail, ensure you correctly set `SLACK_BOT_TOKEN`, `PAPERTRAIL_URL`, and `PAPERTRAIL_PORT`.

### Step 2: Loading Environment Variables and Configurations

After configuring your `.env` file, you need to load the environment variables in your application and specify the path to the logging configuration file. This is done in the initialization of the application.

Here’s how to set it up in your `application.php` file:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use KaririCode\Logging\Util\Config;

// Load environment variables from the .env file
Config::loadEnv();

// Specify the path to the logging configuration file
$configPath = __DIR__ . '/../config/logging.php';

// Initialize the logger configuration
$loggerConfig = new LoggerConfiguration();
$loggerConfig->load($configPath);

// Create the logger factory and registry
$loggerFactory = new LoggerFactory($loggerConfig);
$loggerRegistry = new LoggerRegistry();

// Register the loggers using the service provider
$serviceProvider = new LoggerServiceProvider(
    $loggerConfig,
    $loggerFactory,
    $loggerRegistry
);
$serviceProvider->register();
```

### Step 3: Logging Example

Once the environment variables and the configuration are loaded, you can start using the loggers. Here's an example of logging messages at different levels:

```php
$defaultLogger = $loggerRegistry->getLogger('console');

// Log messages with different severity levels
$defaultLogger->debug('User email is john.doe@example.com');
$defaultLogger->info('User IP is 192.168.1.1');
$defaultLogger->notice('User credit card number is 1234-5678-1234-5678', ['context' => 'credit card']);
$defaultLogger->warning('User phone number is (11) 91234-7890', ['context' => 'phone']);
$defaultLogger->error('An error occurred with email john.doe@example.com', ['context' => 'error']);
$defaultLogger->critical('Critical issue with IP 192.168.1.1', ['context' => 'critical']);
$defaultLogger->alert('Alert regarding credit card 1234-5678-1234-5678', ['context' => 'alert']);
$defaultLogger->emergency('Emergency with phone number 123-456-7890', ['context' => 'emergency']);
```

### Step 4: Using Specialized Loggers

The KaririCode Logging Component also supports specialized loggers, such as for asynchronous logging, query logging, and performance logging. Here’s how you can use these loggers:

```php
// Asynchronous logger
$asyncLogger = $loggerRegistry->getLogger('async');
if ($asyncLogger) {
    for ($i = 0; $i < 3; ++$i) {
        $asyncLogger->info("Async log message {$i}", ['context' => "batch {$i}"]);
    }
}

// Query logger for database queries
$queryLogger = $loggerRegistry->getLogger('query');
$queryLogger->info('Executing query', ['query' => 'SELECT * FROM users', 'bindings' => []]);

// Performance logger to track execution time
$performanceLogger = $loggerRegistry->getLogger('performance');
$performanceLogger->debug('Performance log', ['execution_time' => 1000]);

// Error logger for handling critical errors
$errorLogger = $loggerRegistry->getLogger('error');
$errorLogger->error('A critical error occurred', ['context' => 'Error details']);
```

### Step 5: Sending Critical Logs to Slack

If you've configured Slack as a logging channel in the `.env` file, you can send critical logs directly to a specified Slack channel:

```php
$slackLogger = $loggerRegistry->getLogger('slack');
$slackLogger->critical('This is a critical message sent to Slack', ['context' => 'slack']);
```

Make sure you’ve set your `SLACK_BOT_TOKEN` and `SLACK_CHANNEL` in the `.env` file for this to work properly.

## Testing

To run tests for the KaririCode Logging Component, you can use PHPUnit. Run the following command inside your Docker container:

```bash
make test
```

For test coverage:

```bash
make coverage
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support and Community

- **Documentation**: [https://kariricode.org](https://kariricode.org)
- **Issue Tracker**: [GitHub Issues](https://github.com/KaririCode-Framework/kariricode-contract/issues)
- **Community**: [KaririCode Club Community](https://kariricode.club)
- **Professional Support**: For enterprise-level support, contact us at support@kariricode.org

## Acknowledgments

- The KaririCode Framework team and contributors.
- The PHP community for their continuous support and inspiration.

---

Built with ❤️ by the KaririCode team. Empowering developers to build more robust and flexible PHP applications.

Maintained by Walmir Silva - [walmir.silva@kariricode.org](mailto:walmir.silva@kariricode.org)
