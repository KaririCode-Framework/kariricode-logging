<?php

declare(strict_types=1);

namespace Tests\KaririCode\Logging\Handler;

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Handler\ConsoleHandler;
use KaririCode\Logging\Handler\FileHandler;
use KaririCode\Logging\Handler\LoggerHandlerFactory;
use KaririCode\Logging\Handler\SlackHandler;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\Util\SlackClient;
use PHPUnit\Framework\TestCase;

class LoggerHandlerFactoryTest extends TestCase
{
    private LoggerHandlerFactory $loggerHandlerFactory;
    private LoggerConfiguration $config;

    protected function setUp(): void
    {
        parent::setUp();

        $logFilePath = sys_get_temp_dir() . '/file.log';

        $this->config = new LoggerConfiguration();
        $this->config->set('handlers', [
            'file' => [
                'class' => FileHandler::class,
                'with' => [
                    'filePath' => $logFilePath,
                ],
            ],
            'console' => ConsoleHandler::class,
            'slack' => SlackHandler::class,
        ]);

        $this->config->set('channels', [
            'default' => [
                'handlers' => ['file', 'console'],
            ],
            'slack' => [
                'handlers' => ['slack'],
            ],
        ]);

        $this->loggerHandlerFactory = new LoggerHandlerFactory();
        $this->loggerHandlerFactory->initializeFromConfiguration($this->config);
    }

    public function testInitializeFromConfiguration(): void
    {
        // Valida se a configuração foi corretamente inicializada
        $reflection = new \ReflectionClass($this->loggerHandlerFactory);
        $handlerMap = $reflection->getProperty('handlerMap');
        $handlerMap->setAccessible(true);

        $this->assertIsArray($handlerMap->getValue($this->loggerHandlerFactory));
        $this->assertArrayHasKey('file', $handlerMap->getValue($this->loggerHandlerFactory));
        $this->assertArrayHasKey('console', $handlerMap->getValue($this->loggerHandlerFactory));
        $this->assertArrayHasKey('slack', $handlerMap->getValue($this->loggerHandlerFactory));
    }

    public function testCreateHandlersForDefaultChannel(): void
    {
        // Criando handlers para o canal 'default'
        $handlers = $this->loggerHandlerFactory->createHandlers('default');

        // Verifica se os handlers são instâncias das classes corretas
        $this->assertIsArray($handlers);
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf(FileHandler::class, $handlers[0]);
        $this->assertInstanceOf(ConsoleHandler::class, $handlers[1]);
    }

    public function testCreateHandlersForSlackChannel(): void
    {
        // Mockando o SlackClient
        $mockSlackClient = $this->createMock(SlackClient::class);

        // Configurando o LoggerConfiguration
        $config = new LoggerConfiguration();
        $config->set('handlers', [
            'slack' => [
                'class' => SlackHandler::class,
                'with' => [
                    'slackClient' => $mockSlackClient, // Passando o mock de SlackClient
                ],
            ],
        ]);

        $config->set('channels', [
            'slack' => [
                'handlers' => ['slack'],
            ],
        ]);

        $loggerHandlerFactory = new LoggerHandlerFactory();
        $loggerHandlerFactory->initializeFromConfiguration($config);

        $handlers = $loggerHandlerFactory->createHandlers('slack');

        $this->assertNotEmpty($handlers);
        $this->assertInstanceOf(SlackHandler::class, $handlers[0]);
    }

    public function testCreateHandlersForNonExistingChannel(): void
    {
        $this->expectException(LoggingException::class);
        $this->expectExceptionMessage('No handlers configured for channel: non_existing');

        $config = new LoggerConfiguration();
        $config->set('channels', [
            'existing_channel' => [
                'handlers' => ['file'],
            ],
        ]);

        $loggerHandlerFactory = new LoggerHandlerFactory();
        $loggerHandlerFactory->initializeFromConfiguration($config);

        $loggerHandlerFactory->createHandlers('non_existing');
    }

    public function testCreateHandlersWithInvalidHandler(): void
    {
        $this->config->set('handlers', [
            'invalid' => 'NonExistentHandlerClass',
        ]);
        $this->loggerHandlerFactory->initializeFromConfiguration($this->config);

        $this->expectException(LoggingException::class);
        $this->expectExceptionMessage('No handlers configured for channel: invalid');

        $this->loggerHandlerFactory->createHandlers('invalid');
    }
}
