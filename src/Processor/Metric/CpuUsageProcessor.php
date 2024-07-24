<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor\Metric;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\AbstractProcessor;

class CpuUsageProcessor extends AbstractProcessor
{
    /**
     * @param LogRecord $record
     */
    public function process(ImmutableValue $record): ImmutableValue
    {
        $cpuUsage = sys_getloadavg()[0];
        $context = array_merge($record->context, ['cpu_usage' => $cpuUsage]);

        return new LogRecord(
            $record->level,
            $record->message,
            $context,
            $record->datetime,
            $record->extra
        );
    }
}
