<?php

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\Util\Config;

return [
    'default' => Config::env('LOG_CHANNEL', 'file'),
    'timezone' => Config::env('LOG_TIMEZONE', 'UTC'),
    'channels' => [
        'file' => [
            'minLevel' => Config::env('LOG_LEVEL', 'debug'),
            'handlers' => [
                'file' => [
                    'with' => ['filePath' => Config::storagePath('logs/file2.log')],
                ],
            ],
            'processors' => [
                'introspection_processor',
                'anonymizer_processor'
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
            'minLevel' => Config::env('LOG_LEVEL', 'debug'),
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
                'anonymizer_processor'
            ],
        ],
        'syslog' => [
            'minLevel' => Config::env('LOG_LEVEL', 'debug'),
            'handlers' => ['syslog'],
        ],
        'slack' => [
            'minLevel' => Config::env('LOG_LEVEL', 'critical'),
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
        'enabled' => Config::env('ASYNC_LOG_ENABLED', true),
        'batch_size' => Config::env('ASYNC_LOG_BATCH_SIZE', 10),
        'channel' => Config::env('ASYNC_LOG_CHANNEL', 'file'),
    ],
    'emergency' => [
        'minLevel' => LogLevel::EMERGENCY,
        'path' => Config::storagePath('logs/emergency.log'),
    ],
    'query' => [
        'enabled' => Config::env('QUERY_LOG_ENABLED', false),
        'channel' => Config::env('QUERY_LOG_CHANNEL', 'file'),
        'threshold' => Config::env('QUERY_LOG_THRESHOLD', 100), // in milliseconds
        'handlers' => [
            'console' => [
                'with' => ['useColors' => true],
            ],
            'file' => [
                'with' => ['filePath' => Config::storagePath('logs/query.log')],
            ],
        ],
    ],
    'performance' => [
        'enabled' => Config::env('PERFORMANCE_LOG_ENABLED', false),
        'channel' => Config::env('PERFORMANCE_LOG_CHANNEL', 'file'),
        'threshold' => Config::env('PERFORMANCE_LOG_THRESHOLD', 1000), // in milliseconds
        'handlers' => [
            'console' => [
                'with' => ['useColors' => true],
            ],
            'file' => [
                'with' => ['filePath' => Config::storagePath('logs/performance.log')],
            ],
        ],
        'processors' => [
            'memory_usage_processor',
            'cpu_usage_processor',
            'execution_time_processor',
        ],
    ],
    'error' => [
        'enabled' => Config::env('ERROR_LOG_ENABLED', true),
        'channel' => Config::env('ERROR_LOG_CHANNEL', 'file'),
        'levels' => [
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY
        ],
    ],
    'log_cleaner' => [
        'enabled' => Config::env('LOG_CLEANER_ENABLED', true),
        'keep_days' => Config::env('LOG_CLEANER_KEEP_DAYS', 30),
        'channels' => ['single', 'file'],
    ],
    'handlers' => [
        'file' => [
            'class' => \KaririCode\Logging\Handler\FileHandler::class,
            'with' => [
                'filePath' => Config::storagePath('logs/file.log'),
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
                    Config::env('SLACK_BOT_TOKEN'),
                    Config::env('SLACK_CHANNEL', '#logs'),
                    new \KaririCode\Logging\Resilience\CircuitBreaker(
                        Config::env('CIRCUIT_BREAKER_FAILURE_THRESHOLD', 3),
                        Config::env('CIRCUIT_BREAKER_RESET_TIMEOUT', 60)
                    ),
                    new \KaririCode\Logging\Resilience\Retry(
                        Config::env('RETRY_MAX_ATTEMPTS', 3),
                        Config::env('RETRY_DELAY', 1000),
                        Config::env('RETRY_MULTIPLIER', 2),
                        Config::env('RETRY_JITTER', 100)
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
            'with' => [
                'stackDepth' => 7
            ]
        ],
        'memory_usage_processor' => [
            'class' => \KaririCode\Logging\Processor\Metric\MemoryUsageProcessor::class,
        ],
        'execution_time_processor' => [
            'class' => \KaririCode\Logging\Processor\Metric\ExecutionTimeProcessor::class,
        ],
        'cpu_usage_processor' => [
            'class' => \KaririCode\Logging\Processor\Metric\CpuUsageProcessor::class,
        ],
        'metrics_processor' => [
            'class' => \KaririCode\Logging\Processor\MetricsProcessor::class,
        ],
        'web_processor' => [
            'class' => \KaririCode\Logging\Processor\WebProcessor::class,
        ],
        'anonymizer_processor' => [
            'class' => \KaririCode\Logging\Processor\AnonymizerProcessor::class,
            'with' => [
                'anonymizer' => new \KaririCode\Logging\Security\Anonymizer([
                    'phone' => new \KaririCode\Logging\Security\Anonymizer\PhoneAnonymizer(),
                    'ip' => new \KaririCode\Logging\Security\Anonymizer\IpAnonymizer(),
                ]),
            ],
        ],
        'encryption_processor' => [
            'class' => \KaririCode\Logging\Processor\EncryptionProcessor::class,
            'with' => [
                'encryptor' => new \KaririCode\Logging\Security\Encryptor(Config::env('LOG_ENCRYPTION_KEY')),
            ],
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
