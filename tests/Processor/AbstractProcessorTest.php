<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogProcessor;
use KaririCode\Contract\Logging\Structural\ProcessorAware;
use KaririCode\Logging\Processor\AbstractProcessor;
use PHPUnit\Framework\TestCase;

final class AbstractProcessorTest extends TestCase
{
    private ConcreteTestProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new ConcreteTestProcessor();
    }

    public function testAddProcessor(): void
    {
        $mockProcessor = $this->createMock(LogProcessor::class);

        $result = $this->processor->addProcessor($mockProcessor);

        $this->assertInstanceOf(ProcessorAware::class, $result);
        $this->assertSame($this->processor, $result);
        $this->assertCount(1, $this->processor->getProcessors());
        $this->assertSame($mockProcessor, $this->processor->getProcessors()[0]);
    }

    public function testGetProcessors(): void
    {
        $mockProcessor1 = $this->createMock(LogProcessor::class);
        $mockProcessor2 = $this->createMock(LogProcessor::class);

        $this->processor->addProcessor($mockProcessor1);
        $this->processor->addProcessor($mockProcessor2);

        $processors = $this->processor->getProcessors();

        $this->assertCount(2, $processors);
        $this->assertSame($mockProcessor1, $processors[0]);
        $this->assertSame($mockProcessor2, $processors[1]);
    }

    public function testHasValidContext(): void
    {
        $validContexts = [
            ['file' => 'test.php'],
            ['line' => 10],
            ['class' => 'TestClass'],
            ['function' => 'testFunction'],
            ['file' => 'test.php', 'line' => 10, 'class' => 'TestClass', 'function' => 'testFunction'],
        ];

        $invalidContexts = [
            [],
            ['foo' => 'bar'],
            ['file' => '', 'line' => '', 'class' => '', 'function' => ''],
        ];

        foreach ($validContexts as $context) {
            $this->assertTrue($this->processor->testHasValidContext($context));
        }

        foreach ($invalidContexts as $context) {
            $this->assertFalse($this->processor->testHasValidContext($context));
        }
    }

    public function testProcessMethod(): void
    {
        $mockRecord = $this->createMock(ImmutableValue::class);

        $result = $this->processor->process($mockRecord);

        $this->assertSame($mockRecord, $result);
    }
}



final class ConcreteTestProcessor extends AbstractProcessor
{
    public function process(ImmutableValue $record): ImmutableValue
    {
        // Implementação simples para teste
        return $record;
    }

    // Expõe o método protegido para teste
    public function testHasValidContext(array $context): bool
    {
        return $this->hasValidContext($context);
    }
}
