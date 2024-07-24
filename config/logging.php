<?php

use KaririCode\Logging\Formatter\JsonFormatter;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\Util\ConfigHelper;

// return [
//     'default' => ConfigHelper::env('LOG_CHANNEL', 'file'),
//     'channels' => [
//         'file' => [
//             'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
//             'handlers' => ['file'],
//         ],
//         // 'slack' => [
//         //     'level' => ConfigHelper::env('LOG_LEVEL', 'critical'),
//         //     'handlers' => ['slack'],
//         // ],

//         'console' => [
//             'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
//             'handlers' => ['console'],
//             'formatter' => ConfigHelper::env('LOG_STDERR_FORMATTER'),
//             'processors' => [
//                 'introspection_processor',
//                 // 'memory_usage_processor',
//                 // 'execution_time_processor',
//                 // 'cpu_usage_processor',
//                 // 'web_processor'
//             ],
//         ],

//         'syslog' => [
//             'level' => ConfigHelper::env('LOG_LEVEL', 'debug'),
//             'handlers' => ['syslog'],

//         ],

//         'custom' => [
//             'handlers' => ['custom'],
//         ],
//     ],
//     'async' => [
//         'enabled' => ConfigHelper::env('ASYNC_LOG_ENABLED', true),
//         'driver' => \KaririCode\Logging\Decorator\AsyncLogger::class,
//         'batch_size' => 10,
//     ],

//     'emergency_logger' => [
//         'path' => ConfigHelper::storagePath('logs/emergency.log'),
//         'level' => LogLevel::EMERGENCY,
//     ],

//     'query_logger' => [
//         'enabled' => ConfigHelper::env('QUERY_LOG_ENABLED', false),
//         'channel' => ConfigHelper::env('QUERY_LOG_CHANNEL', 'file'),
//         'threshold' => ConfigHelper::env('QUERY_LOG_THRESHOLD', 100), // in milliseconds
//         'handlers' => ['console', 'file'],
//         'with' => [
//             'filePath' => ConfigHelper::storagePath('logs/query.log'),
//         ],
//     ],

//     'performance_logger' => [
//         'enabled' => ConfigHelper::env('PERFORMANCE_LOG_ENABLED', false),
//         'channel' => ConfigHelper::env('PERFORMANCE_LOG_CHANNEL', 'file'),
//         'threshold' => ConfigHelper::env('PERFORMANCE_LOG_THRESHOLD', 1000), // in milliseconds
//         'with' => [
//             'filePath' => ConfigHelper::storagePath('logs/performance.log'),
//         ],
//         'processors' => [
//             'memory_usage_processor',
//             'cpu_usage_processor',
//             'execution_time_processor',
//         ],
//     ],

//     'error_logger' => [
//         'enabled' => ConfigHelper::env('ERROR_LOG_ENABLED', true),
//         'channel' => ConfigHelper::env('ERROR_LOG_CHANNEL', 'file'),
//         'levels' => [
//             LogLevel::ERROR,
//             LogLevel::CRITICAL,
//             LogLevel::ALERT,
//             LogLevel::EMERGENCY
//         ],
//     ],

//     'log_cleaner' => [
//         'enabled' => ConfigHelper::env('LOG_CLEANER_ENABLED', true),
//         'keep_days' => ConfigHelper::env('LOG_CLEANER_KEEP_DAYS', 30),
//         'channels' => ['single', 'file'],
//     ],

//     'handlers' => [
//         'file' => [
//             'class' => \KaririCode\Logging\Handler\FileHandler::class,
//             'with' => [
//                 'filePath' => ConfigHelper::storagePath('logs/file.log'),
//             ],
//         ],
//         'console' => [
//             'class' => \KaririCode\Logging\Handler\ConsoleHandler::class,
//             'with' => [
//                 'minLevel' => LogLevel::DEBUG,
//                 'useColors' => true,
//             ],
//         ],
//         // 'slack' => [
//         //     'class' => \KaririCode\Logging\Handler\SlackHandler::class,
//         //     'with' => [
//         //         'slackClient' => new SlackClient(
//         //             'webhookUrl' => ConfigHelper::env('LOG_SLACK_WEBHOOK_URL'),
//         //                 CircuitBreaker $circuitBreaker,
//         //                 Retry $retry,
//         //                 Fallback $fallback,
//         //                 CurlClient $curlClient
//         //         ),
//         //         LoggingLogLevel $minLevel = LogLevel::CRITICAL,
//         //         ?LogFormatter $formatter = null

//         //         'url' => ,
//         //         'username' => 'KaririCode Log',
//         //         'emoji' => ':boom:',
//         //         'bubble' => true,
//         //         'context' => ['from' => 'KaririCode'],
//         //         'channels' => ['alerts'],
//         //     ],
//         // ],
//         'syslog' => [
//             'class' => \KaririCode\Logging\Handler\SyslogUdpHandler::class,
//             'with' => [
//                 'host' => '',
//                 'port' => 0,
//             ],
//         ],
//         'custom' => [
//             'class' => \KaririCode\Logging\Handler\NullHandler::class,
//         ]
//         ],
//     'processors' => [
//         'introspection_processor' => [
//             'class' => \KaririCode\Logging\Processor\IntrospectionProcessor::class,
//             'level' => LogLevel::DEBUG,
//         ],
//         'memory_usage_processor' => [
//             'class' => \KaririCode\Logging\Processor\Metric\MemoryUsageProcessor::class,
//             'level' => LogLevel::DEBUG,
//         ],
//         'execution_time_processor' => [
//             'class' => \KaririCode\Logging\Processor\Metric\ExecutionTimeProcessor::class,
//             'level' => LogLevel::DEBUG,
//         ],
//         'cpu_usage_processor' => [
//             'class' => \KaririCode\Logging\Processor\Metric\CpuUsageProcessor::class,
//             'level' => LogLevel::DEBUG,
//         ],
//         'metrics_processor' => [
//             'class' => \KaririCode\Logging\Processor\MetricsProcessor::class,
//             'level' => LogLevel::DEBUG,
//         ],
//         'web_processor' => [
//             'class' => \KaririCode\Logging\Processor\WebProcessor::class,
//             'level' => LogLevel::INFO,
//         ],
//     ],

//     'formatters' => [
//         'line' => [
//             'class' => \KaririCode\Logging\Formatter\LineFormatter::class,
//             'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
//             'date_format' => 'Y-m-d H:i:s',
//         ],
//         'json' => [
//             'class' => \KaririCode\Logging\Formatter\JsonFormatter::class,
//             'include_stacktraces' => true,
//         ],
//     ],
// ];



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
        'custom' => [
            'handlers' => ['custom'],
        ],
    ],
    'async' => [
        'enabled' => ConfigHelper::env('ASYNC_LOG_ENABLED', true),
        'driver' => \KaririCode\Logging\Decorator\AsyncLogger::class,
        'batch_size' => 10,
    ],
    'emergency_logger' => [
        'path' => ConfigHelper::storagePath('logs/emergency.log'),
        'level' => LogLevel::EMERGENCY,
    ],
    'query_logger' => [
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
    'performance_logger' => [
        'enabled' => ConfigHelper::env('PERFORMANCE_LOG_ENABLED', false),
        'channel' => ConfigHelper::env('PERFORMANCE_LOG_CHANNEL', 'file'),
        'threshold' => ConfigHelper::env('PERFORMANCE_LOG_THRESHOLD', 1000), // in milliseconds
        'handlers' => [
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
    'error_logger' => [
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
