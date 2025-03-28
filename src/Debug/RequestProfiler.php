<?php

namespace PhpDevCommunity\Michel\Core\Debug;

use DateTimeImmutable;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;

final class RequestProfiler
{
    private bool $isStarted = false;
    private float $startTime;
    private int $startMemory;
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
        $this->startMemory = memory_get_usage(true);
        $this->request = $request;
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

        $log = $this->createLog($memoryUsage, $duration);

        $this->isStarted = false;
        return array_merge($log, $this->metadata);
    }

    private function createLog(int $memory, float $duration): array
    {
        $request = $this->request;
        return [
            '@timestamp' => (new DateTimeImmutable())->format('c'),
            'log.level' => 'debug',
            'id' => $request->getAttribute('request_id', 'unknown'),
            'event.duration' => $duration,
            'metrics' => [
                'memory.usage' => $this->convertMemory($memory),
                'load_time.ms' => $duration * 1000,
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
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

}
