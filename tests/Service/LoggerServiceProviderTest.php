<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Service;

use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use PHPUnit\Framework\TestCase;

class LoggerServiceProviderTest extends TestCase
{
    private LoggerServiceProvider $loggerServiceProvider;
    private LoggerConfiguration $config;

    protected function setUp(): void
    {
        $this->loggerServiceProvider = new LoggerServiceProvider();
        $this->config = new LoggerConfiguration();
    }

    public function testRegisterLogger(): void
    {
        $this->config->set('default', 'test');
        $this->config->set('channels', [
            'test' => [
                'driver' => 'single',
                'path' => '/tmp/test_log.log',
                'level' => 'debug',
            ],
        ]);

        $this->loggerServiceProvider->register($this->config);
        $logger = LoggerRegistry::getLogger('test');

        $this->assertNotNull($logger);
    }

    public function testRegisterEmergencyLogger(): void
    {
        $this->config->set('emergency_logger', [
            'path' => '/tmp/emergency_log.log',
            'level' => 'emergency',
        ]);

        $this->loggerServiceProvider->register($this->config);
        $logger = LoggerRegistry::getLogger('emergency');

        $this->assertNotNull($logger);
    }
}
