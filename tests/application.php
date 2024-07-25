<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use KaririCode\Logging\Util\ConfigHelper;

// Carrega o arquivo .env
ConfigHelper::loadEnv();

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

$defaultLogger = $loggerRegistry->getLogger('file');
$defaultLogger->debug('This is a debug message', ['context' => 'debug']);
$defaultLogger->info('This is an info message');
$defaultLogger->notice('This is a notice message', ['context' => 'notice']);
$defaultLogger->warning('This is a warning message', ['context' => 'warning']);
$defaultLogger->error('This is an error message', ['context' => 'error']);
$defaultLogger->critical('This is a critical message', ['context' => 'critical']);
$defaultLogger->alert('This is an alert message', ['context' => 'alert']);
$defaultLogger->emergency('This is an emergency message', ['context' => 'emergency']);

$asyncLogger = $loggerRegistry->getLogger('async');
if ($asyncLogger) {
    for ($i = 0; $i < 2; ++$i) {
        $asyncLogger->info("Async log message {$i}", ['context' => "batch {$i}"]);
    }
}

$queryLogger = $loggerRegistry->getLogger('query');
$queryLogger->info('Executing a query', ['time' => 90, 'query' => 'SELECT * FROM users', 'bindings' => []]);

$queryLogger = $loggerRegistry->getLogger('query');
$queryLogger->info('Executing a query', ['query' => 'SELECT * FROM users', 'bindings' => []]);

$performanceLogger = $loggerRegistry->getLogger('performance');
$performanceLogger->debug('Performance logging', ['execution_time' => 100, 'additional_context' => 'example']);

$performanceLogger = $loggerRegistry->getLogger('performance');
$performanceLogger->debug('Performance logging', ['additional_context' => 'example']);

// // // Testa o error logger
// // if (LoggerRegistry::getLogger('error')) {
// //     $errorLogger = LoggerRegistry::getLogger('error');
// //     $errorLogger->error('This is a critical error.', ['context' => 'Testing error logger']);
// // }

// // // Exemplo de registro de um log de emergência
// // $emergencyLogger = LoggerRegistry::getLogger('emergency');
// // $emergencyLogger->emergency('This is an emergency message.');

// // // Exemplo de registro de um log com processador de introspecção
// // $defaultLogger->info('Testing introspection processor.');

// // // Exemplo de registro de um log com processador de memória
// // $defaultLogger->debug('Testing memory usage processor.', ['memory_usage' => memory_get_usage(true)]);

// // // Exemplo de registro de um log com processador de Git
// // $defaultLogger->info('Testing Git processor.', ['branch' => 'main', 'commit' => '1234567890abcdef']);

// // // Exemplo de registro de um log com processador Web
// // $_SERVER['REQUEST_URI'] = '/test-uri';
// // $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
// // $_SERVER['REQUEST_METHOD'] = 'GET';
// // $_SERVER['SERVER_NAME'] = 'localhost';
// // $_SERVER['HTTP_REFERER'] = 'http://localhost/referrer';

// // $defaultLogger->info('Testing web processor.', [
// //     'url' => ($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'https://' : 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/'),
// //     'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
// //     'http_method' => $_SERVER['REQUEST_METHOD'] ?? null,
// //     'server' => $_SERVER['SERVER_NAME'] ?? null,
// //     'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
// // ]);

// // echo "All loggers tested successfully.\n";
