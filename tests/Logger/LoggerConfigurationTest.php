<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\LoggerConfiguration;
use PHPUnit\Framework\TestCase;

class LoggerConfigurationTest extends TestCase
{
    private LoggerConfiguration $config;

    protected function setUp(): void
    {
        $this->config = new LoggerConfiguration();
    }

    public function testSetAndGet(): void
    {
        $this->config->set('key', 'value');
        $this->assertEquals('value', $this->config->get('key'));
    }

    public function testGetWithDefault(): void
    {
        $this->assertEquals('default', $this->config->get('non_existing_key', 'default'));
    }

    public function testLoad(): void
    {
        $configFile = __DIR__ . '/test_config.php';
        $configContent = <<<PHP
    <?php
    return [
        'default' => 'file',
        'channels' => [
            'file' => [
                'handlers' => ['file'],
            ],
        ],
        'handlers' => [
            'file' => [
                'class' => \KaririCode\Logging\Handler\FileHandler::class,
                'with' => [
                    'filePath' => '/path/to/logs/file.log',
                ],
            ],
        ],
        'processors' => [],
        'formatters' => [
            'line' => [
                'class' => \KaririCode\Logging\Formatter\LineFormatter::class,
                'with' => [
                    'dateFormat' => 'Y-m-d H:i:s',
                ],
            ],
        ],
    ];
    PHP;
        file_put_contents($configFile, $configContent);

        $this->config->load($configFile);

        $this->assertEquals('file', $this->config->get('default'));
        $this->assertIsArray($this->config->get('channels'));
        $this->assertIsArray($this->config->get('handlers'));
        $this->assertIsArray($this->config->get('processors'));
        $this->assertIsArray($this->config->get('formatters'));

        unlink($configFile);
    }

    public function testLoadThrowsException(): void
    {
        $this->expectException(LoggingException::class);
        $this->config->load('/non/existing/path.php');
    }
}
