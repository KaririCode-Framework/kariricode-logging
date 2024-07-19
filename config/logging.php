<?php

use KaririCode\Logging\Formatter\JsonFormatter;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\Util\ConfigHelper;

return [
    'default' => ConfigHelper::env('LOG_CHANNEL', 'single'),

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack', 'syslog'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => ConfigHelper::storagePath('logs/single.log'),
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'bubble' => true,
            'permission' => 0664,
            'locking' => false,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => ConfigHelper::storagePath('logs/daily.log'),
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'bubble' => true,
            'permission' => 0664,
            'locking' => false,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => ConfigHelper::env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'KaririCode Log',
            'emoji' => ':boom:',
            'level' => ConfigHelper::env('LOG_LEVEL', 'critical'),
            'bubble' => true,
            'context' => ['from' => 'KaririCode'],
            'channels' => ['alerts'],
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'handler' => \KaririCode\Logging\Handler\SyslogUdpHandler::class,
            'handler_with' => [
                'host' => ConfigHelper::env('PAPERTRAIL_URL'),
                'port' => ConfigHelper::env('PAPERTRAIL_PORT')
            ],
        ],

        'console' => [
            'driver' => 'monolog',
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'handler' => \KaririCode\Logging\Handler\ConsoleHandler::class,
            'formatter' => ConfigHelper::env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'bubble' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'message_type' => 0,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => \KaririCode\Logging\Handler\NullHandler::class,
        ],

        'emergency' => [
            'path' => ConfigHelper::storagePath('logs/emergency.log'),
            'level' =>  LogLevel::EMERGENCY,
        ],
    ],

    'processors' => [
        'introspection' => [
            'class' => \KaririCode\Logging\Processor\IntrospectionProcessor::class,
            'level' => LogLevel::DEBUG,
        ],
        'memory_usage' => [
            'class' => \KaririCode\Logging\Processor\MemoryUsageProcessor::class,
            'level' => LogLevel::DEBUG,
        ],
        'web_processor' => [
            'class' => \KaririCode\Logging\Processor\WebProcessor::class,
            'level' => LogLevel::INFO,
        ],
    ],

    'formatters' => [
        'default' => [
            'class' => \KaririCode\Logging\Formatter\LineFormatter::class,
            'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'date_format' => 'Y-m-d H:i:s',
            'colors' => true,
            'multiline' => true,
        ],
        'json' => [
            'class' => \KaririCode\Logging\Formatter\JsonFormatter::class,
            'include_stacktraces' => true,
        ],
        'elastic' => [
            'class' => \KaririCode\Logging\Formatter\ElasticFormatter::class,
            'index' => ConfigHelper::env('ELASTIC_LOG_INDEX', 'logging-logs'),
        ],
    ],

    'async' => [
        'enabled' => ConfigHelper::env('ASYNC_LOG_ENABLED', true),
        'driver' => \KaririCode\Logging\Decorator\AsyncLogger::class,
        'batch_size' => 10, // Process logs in batches of 10
    ],

    'emergency_logger' => [
        'path' => ConfigHelper::storagePath('logs/emergency.log'),
        'level' => LogLevel::EMERGENCY,
    ],

    'query_logger' => [
        'enabled' => ConfigHelper::env('QUERY_LOG_ENABLED', false),
        'channel' => ConfigHelper::env('QUERY_LOG_CHANNEL', 'daily'),
        'threshold' => ConfigHelper::env('QUERY_LOG_THRESHOLD', 100), // in milliseconds
        'path' => ConfigHelper::storagePath('logs/query.log'),
        'level' => LogLevel::DEBUG,
        'formatter' => ['class' => JsonFormatter::class],
    ],

    'performance_logger' => [
        'enabled' => ConfigHelper::env('PERFORMANCE_LOG_ENABLED', false),
        'channel' => ConfigHelper::env('PERFORMANCE_LOG_CHANNEL', 'daily'),
        'threshold' => ConfigHelper::env('PERFORMANCE_LOG_THRESHOLD', 1000), // in milliseconds
    ],

    'error_logger' => [
        'enabled' => ConfigHelper::env('ERROR_LOG_ENABLED', true),
        'channel' => ConfigHelper::env('ERROR_LOG_CHANNEL', 'daily'),
        'levels' => [
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY
        ],
    ],

    'log_cleaner' => [
        'enabled' => ConfigHelper::env('LOG_CLEANER_ENABLED', true),
        'keep_days' => ConfigHelper::env('LOG_CLEANER_KEEP_DAYS', 30),
        'channels' => ['single', 'daily'],
    ],
];
