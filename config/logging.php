<?php

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\Util\ConfigHelper;

return [
    'default' => ConfigHelper::env('LOG_CHANNEL', 'file'),
    'channels' => [
        'file' => [
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'handlers' => [
                'file' => [
                    'with' => ['filePath' => ConfigHelper::storagePath('logs/file2.log')],
                ],
            ],
            'processors' => [
                'introspection_processor' => [
                    'class' => \KaririCode\Logging\Processor\IntrospectionProcessor::class,
                    'with' => [
                        'stackDepth' => 7
                    ]
                ],
            ],
            'formatter' => [
                'line' => [
                    'with' => [
                        'dateFormat' => 'Y-m-d H:i:s',
                    ]
                ],
            ],
        ],
        'console' => [
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'handlers' => ['console'],
            'formatter' => [
                'json' => [
                    'with' => [
                        'includeStacktraces' => false,
                    ]
                ]
            ],

            'processors' => [
                'introspection_processor',
            ],
        ],
        'syslog' => [
            'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
            'handlers' => ['syslog'],
        ],
        'slack' => [
            'level' => ConfigHelper::env('LOG_LEVEL', 'critical'),
            'handlers' => ['slack'],
            'formatter' => [
                'json' => [
                    'with' => [
                        'includeStacktraces' => true,
                    ]
                ]
            ],
        ],
        'custom' => [
            'handlers' => ['custom'],
        ],
    ],
    'async' => [
        'enabled' => ConfigHelper::env('ASYNC_LOG_ENABLED', true),
        'batch_size' => ConfigHelper::env('ASYNC_LOG_BATCH_SIZE', 10),
        'channel' => ConfigHelper::env('ASYNC_LOG_CHANNEL', 'file'),
    ],
    'emergency' => [
        'path' => ConfigHelper::storagePath('logs/emergency.log'),
        'level' => LogLevel::EMERGENCY,
    ],
    'query' => [
        'enabled' => ConfigHelper::env('QUERY_LOG_ENABLED', false),
        'channel' => ConfigHelper::env('QUERY_LOG_CHANNEL', 'file'),
        'threshold' => ConfigHelper::env('QUERY_LOG_THRESHOLD', 100), // in milliseconds
        'handlers' => [
            'console' => [
                'with' => ['useColors' => true],
            ],
            'file' => [
                'with' => ['filePath' => ConfigHelper::storagePath('logs/query.log')],
            ],
        ],
    ],
    'performance' => [
        'enabled' => ConfigHelper::env('PERFORMANCE_LOG_ENABLED', false),
        'channel' => ConfigHelper::env('PERFORMANCE_LOG_CHANNEL', 'file'),
        'threshold' => ConfigHelper::env('PERFORMANCE_LOG_THRESHOLD', 1000), // in milliseconds
        'handlers' => [
            'console' => [
                'with' => ['useColors' => true],
            ],
            'file' => [
                'with' => ['filePath' => ConfigHelper::storagePath('logs/performance.log')],
            ],
        ],
        'processors' => [
            'memory_usage_processor',
            'cpu_usage_processor',
            'execution_time_processor',
        ],
    ],
    'error' => [
        'enabled' => ConfigHelper::env('ERROR_LOG_ENABLED', true),
        'channel' => ConfigHelper::env('ERROR_LOG_CHANNEL', 'file'),
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
        'channels' => ['single', 'file'],
    ],
    'handlers' => [
        'file' => [
            'class' => \KaririCode\Logging\Handler\FileHandler::class,
            'with' => [
                'filePath' => ConfigHelper::storagePath('logs/file.log'),
            ],
        ],
        'console' => [
            'class' => \KaririCode\Logging\Handler\ConsoleHandler::class,
            'with' => [
                'minLevel' => LogLevel::DEBUG,
                'useColors' => true,
            ],
        ],
        'syslog' => [
            'class' => \KaririCode\Logging\Handler\SyslogUdpHandler::class,
            'with' => [
                'host' => '',
                'port' => 0,
            ],
        ],
        'slack' => [
            'class' => \KaririCode\Logging\Handler\SlackHandler::class,
            'with' => [
                'slackClient' => \KaririCode\Logging\Util\SlackClient::create(
                    ConfigHelper::env('LOG_SLACK_WEBHOOK_URL'),
                    new \KaririCode\Logging\Resilience\CircuitBreaker(
                        ConfigHelper::env('CIRCUIT_BREAKER_FAILURE_THRESHOLD', 3),
                        ConfigHelper::env('CIRCUIT_BREAKER_RESET_TIMEOUT', 60)
                    ),
                    new \KaririCode\Logging\Resilience\Retry(
                        ConfigHelper::env('RETRY_MAX_ATTEMPTS', 3),
                        ConfigHelper::env('RETRY_DELAY', 1000),
                        ConfigHelper::env('RETRY_MULTIPLIER', 2),
                        ConfigHelper::env('RETRY_JITTER', 100)
                    ),
                    new \KaririCode\Logging\Resilience\Fallback(),
                    new \KaririCode\Logging\Util\CurlClient()
                ),
                'minLevel' => LogLevel::CRITICAL,
            ],
        ],
        'custom' => [
            'class' => \KaririCode\Logging\Handler\NullHandler::class,
        ],
    ],
    'processors' => [
        'introspection_processor' => [
            'class' => \KaririCode\Logging\Processor\IntrospectionProcessor::class,
            'level' => LogLevel::DEBUG,
        ],
        'memory_usage_processor' => [
            'class' => \KaririCode\Logging\Processor\Metric\MemoryUsageProcessor::class,
            'level' => LogLevel::DEBUG,
        ],
        'execution_time_processor' => [
            'class' => \KaririCode\Logging\Processor\Metric\ExecutionTimeProcessor::class,
            'level' => LogLevel::DEBUG,
        ],
        'cpu_usage_processor' => [
            'class' => \KaririCode\Logging\Processor\Metric\CpuUsageProcessor::class,
            'level' => LogLevel::DEBUG,
        ],
        'metrics_processor' => [
            'class' => \KaririCode\Logging\Processor\MetricsProcessor::class,
            'level' => LogLevel::DEBUG,
        ],
        'web_processor' => [
            'class' => \KaririCode\Logging\Processor\WebProcessor::class,
            'level' => LogLevel::INFO,
        ],
    ],
    'formatters' => [
        'line' => [
            'class' => \KaririCode\Logging\Formatter\LineFormatter::class,
            'with' => [
                'dateFormat' => 'Y-m-d H:i:s',
            ]
        ],
        'json' => [
            'class' => \KaririCode\Logging\Formatter\JsonFormatter::class,
            'with' => [
                'includeStacktraces' => true,
            ]
        ],
    ],
];
