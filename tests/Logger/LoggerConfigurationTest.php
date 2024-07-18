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
        file_put_contents($configFile, "<?php return ['key' => 'value'];");

        $this->config->load($configFile);
        $this->assertEquals('value', $this->config->get('key'));

        unlink($configFile);
    }

    public function testLoadThrowsException(): void
    {
        $this->expectException(LoggingException::class);
        $this->config->load('/non/existing/path.php');
    }
}
