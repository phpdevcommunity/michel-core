<?php

namespace PhpDevCommunity\Michel\Core\Debug;

final class DebugDataCollector
{
    private array $data = [];
    private bool $isEnabled;

    public function __construct(bool $isEnabled = false)
    {
        $this->isEnabled = $isEnabled;
    }

    public function add(string $key, $value): void
    {
        if (!$this->isEnabled) {
            return;
        }
        $this->data[$key] = $value;
    }

    public function push(string $key, $value): void
    {
        if (!$this->isEnabled) {
            return;
        }
        $this->data[$key][] = $value;
    }

    public function getData(): array
    {
        return $this->data;
    }

}
