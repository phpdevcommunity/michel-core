<?php

namespace Test\PhpDevCommunity\Michel\Core\Response;

use http\Message\Body;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseTest implements ResponseInterface {
    public function getProtocolVersion(): string
    {
        // TODO: Implement getProtocolVersion() method.
    }

    public function withProtocolVersion(string $version): \Psr\Http\Message\MessageInterface
    {
        // TODO: Implement withProtocolVersion() method.
    }

    public function getHeaders(): array
    {
        // TODO: Implement getHeaders() method.
    }

    public function hasHeader(string $name): bool
    {
        // TODO: Implement hasHeader() method.
    }

    public function getHeader(string $name): array
    {
        // TODO: Implement getHeader() method.
    }

    public function getHeaderLine(string $name): string
    {
        // TODO: Implement getHeaderLine() method.
    }

    public function withHeader(string $name, $value): \Psr\Http\Message\MessageInterface
    {
        // TODO: Implement withHeader() method.
    }

    public function withAddedHeader(string $name, $value): \Psr\Http\Message\MessageInterface
    {
        // TODO: Implement withAddedHeader() method.
    }

    public function withoutHeader(string $name): \Psr\Http\Message\MessageInterface
    {
        // TODO: Implement withoutHeader() method.
    }

    public function getBody(): StreamInterface
    {
        // TODO: Implement getBody() method.
    }

    public function withBody(StreamInterface $body): \Psr\Http\Message\MessageInterface
    {
        // TODO: Implement withBody() method.
    }

    public function getStatusCode(): int
    {
        return 200;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        // TODO: Implement withStatus() method.
    }

    public function getReasonPhrase(): string
    {
        // TODO: Implement getReasonPhrase() method.
    }
};
