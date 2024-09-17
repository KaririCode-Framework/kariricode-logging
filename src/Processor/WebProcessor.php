<?php

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Util\Http\Contract\HttpRequest;
use KaririCode\Logging\Util\Http\ServerHttpRequest;

class WebProcessor extends AbstractProcessor
{
    public function __construct(
        private HttpRequest $request = new ServerHttpRequest()
    ) {
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        $context = $this->buildContext($record->context);

        return new LogRecord(
            $record->level,
            $record->message,
            $context,
            $record->datetime,
            $record->extra
        );
    }

    private function buildContext(array $existingContext): array
    {
        return array_merge($existingContext, [
            'url' => $this->request->getUrl(),
            'ip' => $this->request->getIp(),
            'http_method' => $this->request->getMethod(),
            'server' => $this->request->getServerName(),
            'referrer' => $this->request->getReferrer(),
        ]);
    }
}
