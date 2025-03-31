<?php

/**
 * @var array $profiler [
 * '@timestamp' => (new DateTimeImmutable())->format('c'),
 * 'log.level' => 'debug',
 * 'id' => $request->getAttribute('request_id', 'unknown'),
 * 'event.duration' => $duration,
 * 'metrics' => [
 * 'memory.usage' => $this->convertMemory(memory_get_usage(true)),
 * 'memory.peak' => $this->convertMemory(memory_get_peak_usage(true)),
 * 'load_time.ms' => $duration * 1000,
 * 'load_time.s' => number_format($duration, 3),
 * ],
 * 'http.request' => [
 * 'method' => $request->getMethod(),
 * 'url' => $request->getUri()->__toString(),
 * 'path' => $request->getUri()->getPath(),
 * 'body' => $request->getBody()->getContents(),
 * 'headers' => $request->getHeaders(),
 * 'query' => $request->getQueryParams(),
 * 'post' => $request->getParsedBody() ?? [],
 * 'cookies' => $request->getCookieParams(),
 * 'protocol' => $request->getProtocolVersion(),
 * 'server' => $request->getServerParams(),
 * ],
 * ]
 */
?>
<style>
    .__michel_debug_navbar {
        overflow: hidden;
        background-color: #1e232d;
        position: fixed;
        bottom: 0;
        width: 100%;
        display: flex;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";

    }

    .__michel_debug_navbar a {
        color : rgb(166, 223, 239);
        display: block;
        text-align: center;
        padding: 12px 14px;
        text-decoration: none;
        font-size: 14px;
    }

    .__michel_debug_navbar a:hover {
        background-color: #07193e;
    }
    .__michel_debug_navbar a:hover > .__michel_debug_value {
        transform: scale(1.1);
    }

    .__michel_debug_value {
        font-weight: bold;
        margin-left: 5px;
        font-size: 11px;
        color: #ececec;
        display: inline-block;
    }
</style>
<div class="__michel_debug_navbar">
    <a href="#time" class="active">
        Time <span class="__michel_debug_value"><?php echo $profiler['metrics']['load_time.ms'] ?> ms</span>
    </a>
    <a href="#memory">
        MEMORY <span class="__michel_debug_value"><?php echo $profiler['metrics']['memory.peak.human'] ?></span>
    </a>
    <a href="#request">
        METHOD <span class="__michel_debug_value"><?php echo $profiler['http.request']['method'] ?></span>
    </a>
    <a href="#env">
        ENV <span class="__michel_debug_value"><?php echo strtoupper($profiler['environment']) ?></span>
    </a>
    <a href="#php_version">PHP <span class="__michel_debug_value"><?php echo $profiler['php_version'] ?> 🐘</span> </a>
</div>

