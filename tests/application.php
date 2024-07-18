<?php

require_once __DIR__ . '/../vendor/autoload.php';

use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use KaririCode\Logging\Util\ConfigHelper;

// Carrega o arquivo .env
ConfigHelper::loadEnv();

// Inicialização e execução da aplicação
$configPath = __DIR__ . '/../config/logging.php';
$loggerConfig = new LoggerConfiguration();
$loggerConfig->load($configPath);

$serviceProvider = new LoggerServiceProvider();
$serviceProvider->register($loggerConfig);

// / Obtém o logger padrão
$defaultLogger = LoggerRegistry::getLogger('default');

// Testa o logger padrão
$defaultLogger->info('This is an info message.');
$defaultLogger->error('This is an error message.');

// Testa o logger assíncrono
if (LoggerRegistry::getLogger('async') instanceof AsyncLogger) {
    $asyncLogger = LoggerRegistry::getLogger('async');
    $asyncLogger->info('This is an async info message.');
    $asyncLogger->error('This is an async error message.');
}

// Testa o query logger
if (LoggerRegistry::getLogger('query')) {
    $queryLogger = LoggerRegistry::getLogger('query');
    $queryLogger->debug('Executing query...', ['query' => 'SELECT * FROM users', 'bindings' => []]);
}

// Testa o performance logger
if (LoggerRegistry::getLogger('performance')) {
    $performanceLogger = LoggerRegistry::getLogger('performance');
    $performanceLogger->debug('Performance logging', ['execution_time' => 1500]);
}

// Testa o error logger
if (LoggerRegistry::getLogger('error')) {
    $errorLogger = LoggerRegistry::getLogger('error');
    $errorLogger->error('This is a critical error.', ['context' => 'Testing error logger']);
}

// Exemplo de registro de um log de emergência
$emergencyLogger = LoggerRegistry::getLogger('emergency');
$emergencyLogger->emergency('This is an emergency message.');

// Exemplo de registro de um log com processador de introspecção
$defaultLogger->info('Testing introspection processor.');

// Exemplo de registro de um log com processador de memória
$defaultLogger->debug('Testing memory usage processor.', ['memory_usage' => memory_get_usage(true)]);

// Exemplo de registro de um log com processador de Git
$defaultLogger->info('Testing Git processor.', ['branch' => 'main', 'commit' => '1234567890abcdef']);

// Exemplo de registro de um log com processador Web
$_SERVER['REQUEST_URI'] = '/test-uri';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_REFERER'] = 'http://localhost/referrer';

$defaultLogger->info('Testing web processor.', [
    'url' => ($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'https://' : 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'http_method' => $_SERVER['REQUEST_METHOD'] ?? null,
    'server' => $_SERVER['SERVER_NAME'] ?? null,
    'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
]);

echo "All loggers tested successfully.\n";
