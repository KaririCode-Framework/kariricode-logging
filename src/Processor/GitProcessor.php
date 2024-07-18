<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;

class GitProcessor extends AbstractProcessor
{
    private ?string $branch = null;
    private ?string $commit = null;

    public function __construct()
    {
        $this->detectGitInfo();
    }

    private function detectGitInfo(): void
    {
        if (file_exists('.git/HEAD')) {
            $headContent = file_get_contents('.git/HEAD');
            if (preg_match('#ref: refs/heads/(.+)#', $headContent, $matches)) {
                $this->branch = trim($matches[1]);
            }
        }

        if (file_exists('.git/refs/heads/' . $this->branch)) {
            $this->commit = trim(file_get_contents('.git/refs/heads/' . $this->branch));
        }
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        $context = $record->context;

        if ($this->branch) {
            $context['git']['branch'] = $this->branch;
        }
        if ($this->commit) {
            $context['git']['commit'] = $this->commit;
        }

        return new LogRecord(
            $record->level,
            $record->message,
            $context,
            $record->datetime,
            $record->extra
        );
    }
}
