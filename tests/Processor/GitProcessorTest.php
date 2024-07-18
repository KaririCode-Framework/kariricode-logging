<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Processor;

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\GitProcessor;
use PHPUnit\Framework\TestCase;

class GitProcessorTest extends TestCase
{
    private GitProcessor $gitProcessor;

    protected function setUp(): void
    {
        $this->gitProcessor = new GitProcessor();
    }

    public function testProcessHappyPath(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $processedRecord = $this->gitProcessor->process($record);

        $this->assertArrayHasKey('git', $processedRecord->context);
    }

    public function testProcessWithGitInfo(): void
    {
        // Simulate Git info
        file_put_contents('.git/HEAD', 'ref: refs/heads/main');
        file_put_contents('.git/refs/heads/main', 'commit_hash');

        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $processedRecord = $this->gitProcessor->process($record);

        $this->assertEquals('main', $processedRecord->context['git']['branch']);
        $this->assertEquals('commit_hash', $processedRecord->context['git']['commit']);

        // Clean up
        unlink('.git/HEAD');
        unlink('.git/refs/heads/main');
    }
}
