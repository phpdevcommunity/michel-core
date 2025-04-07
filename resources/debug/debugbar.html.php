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
        background-color: #1e232d;
        position: fixed;
        right: 0;
        bottom: 0;
        width: 100%;
        display: flex;
        height: 40px;
        font-family: Inter, sans-serif;
        white-space: nowrap;
    }

    .__michel_debug_navbar a {
        color: rgb(166, 223, 239);
        display: block;
        text-align: center;
        padding: 12px 14px;
        text-decoration: none;
        font-size: 12px;
    }

    .__michel_debug_navbar a:hover {
        background-color: #07193e;
    }

    .__michel_debug_navbar a:hover > .__michel_debug_value {
        transform: scale(1.05);
    }

    .__michel_debug_value {
        font-weight: bold;
        font-size: 11px;
        color: #ececec;
        display: inline-block;
    }

    .__michel_dropup {
        position: relative;
        display: inline-block;
    }

    .__michel_dropup-content {
        font-size: 12px;
        display: none;
        position: absolute;
        background-color: #1e232d;
        max-width: 450px;
        max-height: 480px;
        bottom: 40px;
        overflow: hidden;
        overflow-y: auto;
        padding: 10px;
        z-index: 100000;
        vertical-align: baseline;
        letter-spacing: normal;
        white-space: nowrap;

    }

    .__michel_dropup-content a:hover {
        background-color: inherit;
    }

    .__michel_dropup:hover .__michel_dropup-content {
        display: block;
    }

    .__michel_table {
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
        border: 0px;
    }

    .__michel_table th, td {
        text-align: left;
        padding: 6px;
    }

    .__michel_label {
        padding: 4px;
    }

    .__michel_label_success {
        background-color: #04AA6D;
    }

    /* Green */
    .__michel_label_info {
        background-color: #2196F3;
    }

    /* Blue */
    .__michel_label_warning {
        background-color: #ff9800;
    }

    /* Orange */
    .__michel_label_danger {
        background-color: #f44336;
    }

    /* Red */
    .__michel_label_other {
        background-color: #e7e7e7;
        color: black;
    }

    /* Gray */
</style>
<div class="__michel_debug_navbar">
    <div class="__michel_dropup">
        <a href="#response">
            <?php if ($profiler['__response_code'] >= 200 && $profiler['__response_code'] < 300) : ?>
                🚦 <span class="__michel_label __michel_label_success"><span
                            class="__michel_debug_value"><?php echo $profiler['__response_code'] ?></span></span>
            <?php elseif ($profiler['__response_code'] >= 300 && $profiler['__response_code'] < 400) : ?>
                🚦 <span class="__michel_label __michel_label_info"><span
                            class="__michel_debug_value"><?php echo $profiler['__response_code'] ?></span></span>
            <?php elseif ($profiler['__response_code'] >= 400 && $profiler['__response_code'] < 500) : ?>
                🚦 <span class="__michel_label __michel_label_warning"><span
                            class="__michel_debug_value"><?php echo $profiler['__response_code'] ?></span></span>
            <?php else : ?>
                🚦 <span class="__michel_label __michel_label_danger"><span
                            class="__michel_debug_value"><?php echo $profiler['__response_code'] ?></span></span>
            <?php endif; ?>
        </a>
        <div class="__michel_dropup-content">
            <table class="__michel_table">
                <tr>
                    <td>HTTP status <Ver></Ver></td>
                    <td class="__michel_debug_value" title="<?php echo $profiler['__response_code'] ?>"><?php echo $profiler['__response_code'] ?></td>
                </tr>
                <?php if (isset($profiler['__controller'])) : ?>
                    <tr>
                        <td>Controller</td>
                        <td class="__michel_debug_value" title="<?php echo $profiler['__controller'] ?>"><?php echo $profiler['__controller'] ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (isset($profiler['__route_name'])) : ?>
                    <tr>
                        <td>Route name</td>
                        <td class="__michel_debug_value" title="<?php echo $profiler['__route_name'] ?>"><?php echo $profiler['__route_name'] ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <a href="#time">
        🕒 [REQ] <span class="__michel_debug_value"><?php echo $profiler['metrics']['load_time.ms'] ?> ms</span>
    </a>
    <a href="#memory">
        💾 [MEM] <span class="__michel_debug_value"><?php echo $profiler['metrics']['memory.peak.human'] ?></span>
    </a>
    <a href="#request">
        🌐 [METHOD] <span class="__michel_debug_value"><?php echo $profiler['http.request']['method'] ?></span>
    </a>
    <a href="#env">
        🛠️ [ENV] <span class="__michel_debug_value"><?php echo strtoupper($profiler['environment']) ?></span>
    </a>
    <div class="__michel_dropup">
        <a href="#">
            🐘 [PHP] <span class="__michel_debug_value"><?php echo $profiler['php_version'] ?></span>
        </a>
        <div class="__michel_dropup-content">
            <table class="__michel_table">
                <tr>
                    <td>PHP Version</td>
                    <td class="__michel_debug_value"
                        title="<?php echo $profiler['php_version'] ?>"><?php echo $profiler['php_version'] ?></td>
                </tr>
                <tr>
                    <td>PHP Extensions</td>
                    <td class="__michel_debug_value"
                        title="<?php echo $profiler['php_extensions'] ?>"><?php echo $profiler['php_extensions'] ?></td>
                </tr>
                <tr>
                    <td>PHP SAPI</td>
                    <td class="__michel_debug_value"
                        title="<?php echo $profiler['php_sapi'] ?>"><?php echo $profiler['php_sapi'] ?></td>
                </tr>
                <tr>
                    <td>PHP Memory Limit</td>
                    <td class="__michel_debug_value"
                        title="<?php echo $profiler['php_memory_limit'] ?>"><?php echo $profiler['php_memory_limit'] ?></td>
                </tr>
                <tr>
                    <td>PHP Timezone</td>
                    <td class="__michel_debug_value"
                        title="<?php echo $profiler['php_timezone'] ?>"><?php echo $profiler['php_timezone'] ?></td>
                </tr>
            </table>
        </div>
    </div>
    <?php if (isset($profiler['__middlewares_executed'])) : ?>
        <div class="__michel_dropup">
            <a href="#" title="Middlewares executed">
                🔀 [MID] <span class="__michel_debug_value"><?php echo count($profiler['__middlewares_executed']) ?></span>
            </a>
            <div class="__michel_dropup-content">
                <table class="__michel_table">
                    <?php foreach ($profiler['__middlewares_executed'] as $index => $middleware) : ?>
                        <tr>
                            <td>
                                <span class="__michel_debug_value"><?php echo sprintf('%s. %s', $index + 1, $middleware) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
