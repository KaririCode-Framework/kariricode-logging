<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogProcessor;
use KaririCode\Contract\Logging\Structural\ProcessorAware;

abstract class AbstractProcessor implements ProcessorAware
{
    protected LogProcessor $processor;
    private array $processors = [];

    protected function hasValidContext(array $context): bool
    {
        return !empty($context['file']) || !empty($context['line']) || !empty($context['class']) || !empty($context['function']);
    }

    abstract public function process(ImmutableValue $record): ImmutableValue;

    public function addProcessor(LogProcessor $processor): ProcessorAware
    {
        $this->processors[] = $processor;

        return $this;
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }
}
