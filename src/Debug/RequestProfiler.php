<?php

namespace PhpDevCommunity\Michel\Core\Debug;

use DateTimeImmutable;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;

final class RequestProfiler
{
    private bool $isStarted = false;
    private float $startTime;
    private ServerRequestInterface $request;

    private array $metadata;

    public function __construct(array $metadata = [])
    {
        $this->metadata = $metadata;
    }

    public function start(ServerRequestInterface $request): void
    {
        if ($this->isStarted) {
            throw new LogicException('Please call stop() before start()');
        }

        $this->isStarted = true;
        $this->startTime = microtime(true);
        $this->request = $request;
    }

    public function stop(): array
    {
        if ($this->isStarted === false) {
            throw new LogicException('Please call start() before stop()');
        }

        $endTime = microtime(true);
        $duration = $endTime - $this->startTime;

        $log = $this->createLog($duration);

        $this->isStarted = false;
        return array_merge($log, $this->metadata);
    }

    private function createLog(float $duration): array
    {
        $request = $this->request;
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        return [
            '@timestamp' => (new DateTimeImmutable())->format('c'),
            'log.level' => 'debug',
            'id' => $request->getAttribute('request_id', 'unknown'),
            'event.duration' => $duration,
            'metrics' => [
                'memory.usage' => $memoryUsage,
                'memory.usage.human' => $this->convertMemory($memoryUsage),
                'memory.peak' => $memoryPeak,
                'memory.peak.human' => $this->convertMemory(memory_get_peak_usage(true)),
                'load_time.ms' => number_format($duration * 1000, 3),
                'load_time.s' => number_format($duration, 3),
            ],
            'http.request' => [
                'method' => $request->getMethod(),
                'url' => $request->getUri()->__toString(),
                'path' => $request->getUri()->getPath(),
                'body' => $request->getBody()->getContents(),
                'headers' => $request->getHeaders(),
                'query' => $request->getQueryParams(),
                'post' => $request->getParsedBody() ?? [],
                'cookies' => $request->getCookieParams(),
                'protocol' => $request->getProtocolVersion(),
                'server' => $request->getServerParams(),
            ],
        ];
    }

    private function convertMemory(int $size): string
    {
        $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

}
