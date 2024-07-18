<?php

declare(strict_types=1);

namespace KaririCode\Logging\Resilience;

class Fallback
{
    public function execute(callable $primaryOperation, ?callable $fallbackOperation = null): mixed
    {
        $primary = \Closure::fromCallable($primaryOperation);
        $fallback = $fallbackOperation ? \Closure::fromCallable($fallbackOperation) : null;

        try {
            return $primary();
        } catch (\Throwable $e) {
            if (is_null($fallback)) {
                throw $e;
            }

            return $fallback($e);
        }
    }
}
