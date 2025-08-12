<?php

declare(strict_types=1);

/**
 * KaririCode Logging Framework - Demonstration Application.
 *
 * This application demonstrates the comprehensive logging capabilities of the
 * KaririCode Logging Framework, showcasing various loggers, formatters, and
 * processors in action.
 *
 * @author KaririCode Team
 * @license MIT
 *
 * @see https://kariricode.org/
 */

require_once __DIR__ . '/../vendor/autoload.php';

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use KaririCode\Logging\Util\Config;

/**
 * Main Application class that demonstrates KaririCode Logging capabilities.
 *
 * This class follows SOLID principles and Clean Code practices, providing
 * a well-structured demonstration of the logging framework features.
 */
final class LoggingDemoApplication
{
    private const CONFIG_PATH = __DIR__ . '/../config/logging.php';
    private const DEFAULT_LOGGER = 'console';

    private LoggerRegistry $loggerRegistry;
    private bool $isInitialized = false;

    /**
     * Initialize the logging application.
     *
     * @throws LoggingException When initialization fails
     */
    public function __construct()
    {
        $this->initializeEnvironment();
        $this->initializeLoggingSystem();
        $this->isInitialized = true;
    }

    /**
     * Run the complete logging demonstration.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function run(): int
    {
        try {
            $this->validateInitialization();

            echo "🚀 KaririCode Logging Framework - Demo Application\n";
            echo str_repeat('=', 60) . "\n\n";

            $this->runSecurityDemonstration();
            $this->runAsyncLoggingDemonstration();
            $this->runSpecializedLoggersDemonstration();
            $this->runSlackIntegrationDemonstration();

            echo "\n✅ All logging demonstrations completed successfully!\n";
            echo "📁 Check the logs/ directory for generated log files.\n\n";

            return 0;
        } catch (Throwable $e) {
            $this->handleError($e);

            return 1;
        }
    }

    /**
     * Initialize environment configuration.
     *
     * @throws LoggingException When environment loading fails
     */
    private function initializeEnvironment(): void
    {
        try {
            Config::loadEnv();
        } catch (Throwable $e) {
            throw new LoggingException('Failed to load environment configuration: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Initialize the logging system with proper configuration.
     *
     * @throws LoggingException When logging system initialization fails
     */
    private function initializeLoggingSystem(): void
    {
        try {
            if (!file_exists(self::CONFIG_PATH)) {
                throw new LoggingException('Logging configuration file not found: ' . self::CONFIG_PATH);
            }

            $loggerConfig = new LoggerConfiguration();
            $loggerConfig->load(self::CONFIG_PATH);

            $loggerFactory = new LoggerFactory($loggerConfig);
            $this->loggerRegistry = new LoggerRegistry();

            $serviceProvider = new LoggerServiceProvider(
                $loggerConfig,
                $loggerFactory,
                $this->loggerRegistry
            );

            $serviceProvider->register();
        } catch (Throwable $e) {
            throw new LoggingException('Failed to initialize logging system: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Validate that the application is properly initialized.
     *
     * @throws LoggingException When validation fails
     */
    private function validateInitialization(): void
    {
        if (!$this->isInitialized) {
            throw new LoggingException('Application is not properly initialized');
        }

        try {
            $this->loggerRegistry->getLogger(self::DEFAULT_LOGGER);
        } catch (Throwable $e) {
            throw new LoggingException('Default logger not available: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Demonstrate security-focused logging with data anonymization.
     */
    private function runSecurityDemonstration(): void
    {
        echo "🔒 Security & Anonymization Demo\n";
        echo str_repeat('-', 40) . "\n";

        $logger = $this->loggerRegistry->getLogger(self::DEFAULT_LOGGER);

        // Demonstrate different log levels with sensitive data
        $securityScenarios = [
            ['debug', 'User authentication attempt', ['email' => 'john.doe@example.com']],
            ['info', 'User login from IP', ['ip' => '192.168.1.1', 'user_agent' => 'Mozilla/5.0']],
            ['notice', 'Payment processing initiated', ['card' => '1234-5678-1234-5678', 'amount' => 99.99]],
            ['warning', 'Suspicious activity detected', ['phone' => '(11) 91234-7890', 'attempts' => 3]],
            ['error', 'Failed login attempt', ['email' => 'admin@example.com', 'ip' => '192.168.1.100']],
            ['critical', 'Security breach detected', ['ip' => '10.0.0.1', 'affected_users' => 150]],
            ['alert', 'Credit card fraud alert', ['card' => '4111-1111-1111-1111', 'transaction_id' => 'TXN123']],
            ['emergency', 'System compromise detected', ['phone' => '555-123-4567', 'severity' => 'high']],
        ];

        foreach ($securityScenarios as [$level, $message, $context]) {
            $logger->$level($message, $context);
        }

        echo "✅ Security logging completed - sensitive data anonymized\n\n";
    }

    /**
     * Demonstrate asynchronous logging capabilities.
     */
    private function runAsyncLoggingDemonstration(): void
    {
        echo "⚡ Asynchronous Logging Demo\n";
        echo str_repeat('-', 40) . "\n";

        try {
            $asyncLogger = $this->loggerRegistry->getLogger('async');

            echo "Generating async log messages...\n";

            for ($i = 1; $i <= 5; ++$i) {
                $asyncLogger->info("Async batch operation {$i}", [
                    'batch_id' => $i,
                    'process_id' => getmypid(),
                    'timestamp' => microtime(true),
                    'memory_usage' => memory_get_usage(true),
                ]);

                // Simulate some processing time
                usleep(100000); // 0.1 second
            }

            echo "✅ Async logging completed - check for batched output\n\n";
        } catch (Throwable $e) {
            echo "⚠️  Async logger not available: {$e->getMessage()}\n\n";
        }
    }

    /**
     * Demonstrate specialized loggers (Query, Performance, Error).
     */
    private function runSpecializedLoggersDemonstration(): void
    {
        echo "🎯 Specialized Loggers Demo\n";
        echo str_repeat('-', 40) . "\n";

        $this->demonstrateQueryLogger();
        $this->demonstratePerformanceLogger();
        $this->demonstrateErrorLogger();
    }

    /**
     * Demonstrate query logging with execution time tracking.
     */
    private function demonstrateQueryLogger(): void
    {
        try {
            $queryLogger = $this->loggerRegistry->getLogger('query');

            echo "📊 Query Logger - Database operation simulation\n";

            $queryScenarios = [
                [
                    'query' => 'SELECT * FROM users WHERE active = ? AND created_at > ?',
                    'bindings' => [true, '2024-01-01'],
                    'time' => 45.2,
                ],
                [
                    'query' => 'SELECT COUNT(*) FROM orders WHERE status = ?',
                    'bindings' => ['completed'],
                    'time' => 120.8, // Slow query - should trigger warning
                ],
                [
                    'query' => 'INSERT INTO audit_log (user_id, action, timestamp) VALUES (?, ?, ?)',
                    'bindings' => [123, 'login', time()],
                    'time' => 15.3,
                ],
            ];

            foreach ($queryScenarios as $scenario) {
                $queryLogger->info('Database query executed', $scenario);
            }

            echo "✅ Query logging completed\n";
        } catch (Throwable $e) {
            echo "⚠️  Query logger not available: {$e->getMessage()}\n";
        }
    }

    /**
     * Demonstrate performance logging with metrics.
     */
    private function demonstratePerformanceLogger(): void
    {
        try {
            $performanceLogger = $this->loggerRegistry->getLogger('performance');

            echo "📈 Performance Logger - Application metrics\n";

            $performanceMetrics = [
                [
                    'operation' => 'user_authentication',
                    'execution_time' => 250.5,
                    'memory_peak' => '2.5MB',
                    'cpu_usage' => '15%',
                ],
                [
                    'operation' => 'report_generation',
                    'execution_time' => 1500.2, // Slow operation
                    'memory_peak' => '128MB',
                    'cpu_usage' => '85%',
                ],
                [
                    'operation' => 'cache_warming',
                    'execution_time' => 750.1,
                    'memory_peak' => '64MB',
                    'items_processed' => 1000,
                ],
            ];

            foreach ($performanceMetrics as $metrics) {
                $performanceLogger->debug('Performance metrics collected', $metrics);
            }

            echo "✅ Performance logging completed\n";
        } catch (Throwable $e) {
            echo "⚠️  Performance logger not available: {$e->getMessage()}\n";
        }
    }

    /**
     * Demonstrate error logging with context.
     */
    private function demonstrateErrorLogger(): void
    {
        try {
            $errorLogger = $this->loggerRegistry->getLogger('error');

            echo "🚨 Error Logger - Exception handling simulation\n";

            $errorScenarios = [
                [
                    'level' => 'error',
                    'message' => 'Database connection timeout',
                    'context' => [
                        'host' => 'db-server-01',
                        'port' => 3306,
                        'timeout' => 30,
                        'retry_count' => 3,
                    ],
                ],
                [
                    'level' => 'critical',
                    'message' => 'Payment gateway API failure',
                    'context' => [
                        'gateway' => 'stripe',
                        'error_code' => 'card_declined',
                        'transaction_amount' => 199.99,
                        'user_id' => 12345,
                    ],
                ],
            ];

            foreach ($errorScenarios as $scenario) {
                $level = $scenario['level'];
                $errorLogger->$level($scenario['message'], $scenario['context']);
            }

            echo "✅ Error logging completed\n";
        } catch (Throwable $e) {
            echo "⚠️  Error logger not available: {$e->getMessage()}\n";
        }
    }

    /**
     * Demonstrate Slack integration for critical alerts.
     */
    private function runSlackIntegrationDemonstration(): void
    {
        echo "\n💬 Slack Integration Demo\n";
        echo str_repeat('-', 40) . "\n";

        try {
            $slackLogger = $this->loggerRegistry->getLogger('slack');

            echo "Sending critical alert to Slack...\n";

            $slackLogger->critical('🚨 CRITICAL ALERT: System performance degraded', [
                'server' => 'web-server-01',
                'cpu_usage' => '95%',
                'memory_usage' => '98%',
                'active_connections' => 1500,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);

            echo "✅ Slack notification sent successfully\n";
        } catch (Throwable $e) {
            echo "⚠️  Slack integration not available: {$e->getMessage()}\n";
            echo "💡 Configure SLACK_BOT_TOKEN in .env to enable Slack logging\n";
        }
    }

    /**
     * Handle application errors gracefully.
     *
     * @param Throwable $error The error that occurred
     */
    private function handleError(Throwable $error): void
    {
        $errorMessage = sprintf(
            "❌ Application Error: %s\n📍 File: %s:%d\n",
            $error->getMessage(),
            $error->getFile(),
            $error->getLine()
        );

        echo $errorMessage;

        // Try to log the error if possible
        if ($this->isInitialized) {
            try {
                $logger = $this->loggerRegistry->getLogger(self::DEFAULT_LOGGER);
                $logger->emergency('Application crashed with error', [
                    'error' => $error->getMessage(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                    'trace' => $error->getTraceAsString(),
                ]);
            } catch (Throwable) {
                // If logging fails, just continue with graceful shutdown
            }
        }
    }
}

// ============================================================================
// Application Bootstrap and Execution
// ============================================================================

/**
 * Application entry point with proper error handling.
 */
function main(): int
{
    try {
        $application = new LoggingDemoApplication();

        return $application->run();
    } catch (Throwable $e) {
        echo "💥 Fatal Error: Failed to initialize application\n";
        echo "📝 Error: {$e->getMessage()}\n";
        echo "📍 Location: {$e->getFile()}:{$e->getLine()}\n";

        return 1;
    }
}

// Execute the application if run directly
if ('application.php' === basename($_SERVER['SCRIPT_NAME'])) {
    exit(main());
}
