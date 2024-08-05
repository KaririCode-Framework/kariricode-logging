<?php

// application.php
require_once __DIR__ . '/../vendor/autoload.php';

use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use KaririCode\Logging\Util\Config;

// Carrega o arquivo .env
Config::loadEnv();

// Inicialização e execução da aplicação
$configPath = __DIR__ . '/../config/logging.php';
$loggerConfig = new LoggerConfiguration();
$loggerConfig->load($configPath);

$loggerFactory = new LoggerFactory($loggerConfig);
$loggerRegistry = new LoggerRegistry();
$serviceProvider = new LoggerServiceProvider(
    $loggerConfig,
    $loggerFactory,
    $loggerRegistry
);

$serviceProvider->register();

// $defaultLogger = $loggerRegistry->getLogger('console');

// $defaultLogger->debug('User email is john.doe@example.com');
// $defaultLogger->info('User IP is 192.168.1.1');
// $defaultLogger->notice('User credit card number is 1234-5678-1234-5678', ['context' => 'credit card']);
// $defaultLogger->warning('User phone number is (11) 91234-7890', ['context' => 'phone']);
// $defaultLogger->error('This is an error message with email john.doe@example.com', ['context' => 'error']);
// $defaultLogger->critical('This is a critical message with IP 192.168.1.1', ['context' => 'critical']);
// $defaultLogger->alert('This is an alert message with credit card 1234-5678-1234-5678', ['context' => 'alert']);
// $defaultLogger->emergency('This is an emergency message with phone number 123-456-7890', ['context' => 'emergency']);

// $asyncLogger = $loggerRegistry->getLogger('async');
// if ($asyncLogger) {
//     for ($i = 0; $i < 3; ++$i) {
//         $asyncLogger->info("Async log message {$i}", ['context' => "batch {$i}"]);
//     }
// }

// $queryLogger = $loggerRegistry->getLogger('query');
// $queryLogger->info('Executing a query', ['time' => 90, 'query' => 'SELECT * FROM users', 'bindings' => []]);

// $queryLogger = $loggerRegistry->getLogger('query');
// $queryLogger->info('Executing a query', ['query' => 'SELECT * FROM users', 'bindings' => []]);

// $performanceLogger = $loggerRegistry->getLogger('performance');
// $performanceLogger->debug('Performance logging', ['execution_time' => 1000, 'additional_context' => 'example']);

// $performanceLogger = $loggerRegistry->getLogger('performance');
// $performanceLogger->debug('Performance logging');

// $errorLogger = $loggerRegistry->getLogger('error');
// $errorLogger->error('This is a critical error.', ['context' => 'Testing error logger']);

$slackLogger = $loggerRegistry->getLogger('slack');
$slackLogger->critical('Este é um teste de mensagem crítica enviada para o Slack');
