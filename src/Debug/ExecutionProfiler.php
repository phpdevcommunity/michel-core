<?php

namespace PhpDevCommunity\Michel\Core\Debug;

use DateTimeImmutable;
use LogicException;

final class ExecutionProfiler
{
    private bool $isStarted = false;
    private float $startTime;
    private int $startMemory;
    private array $metadata;

    public function __construct(array $metadata = [])
    {
        $this->metadata = $metadata;
    }

    public function start(): void
    {
        if ($this->isStarted) {
            throw new LogicException('Please call stop() before start()');
        }
        $this->isStarted = true;
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    public function addMetadata(string $key, $value)
    {
        $this->metadata[$key][] = $value;
    }

    public function stop(): array
    {
        if ($this->isStarted === false) {
            throw new LogicException('Please call start() before stop()');
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $duration = $endTime - $this->startTime;
        $memoryUsage = $endMemory - $this->startMemory;

        $this->isStarted = false;
        return [
                '@timestamp' => (new DateTimeImmutable())->format('c'),
                'log.level' => 'debug',
                'event.duration' => $duration,
                'metrics' => [
                    'memory.usage' => _m_convert($memoryUsage),
                    'peak_memory.usage' => _m_convert(memory_get_peak_usage(true)),
                    'load_time.ms' => $duration * 1000,
                    'load_time.s' => number_format($duration, 3),
                ],
            ] + $this->metadata;
    }
}
