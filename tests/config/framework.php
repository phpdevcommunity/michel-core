<?php


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

return [
    'server_request' => static function (): ServerRequestInterface {
        return new class implements ServerRequestInterface {

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
                return [];
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

            public function getRequestTarget(): string
            {
                // TODO: Implement getRequestTarget() method.
            }

            public function withRequestTarget(string $requestTarget): \Psr\Http\Message\RequestInterface
            {
                // TODO: Implement withRequestTarget() method.
            }

            public function getMethod(): string
            {
                // TODO: Implement getMethod() method.
            }

            public function withMethod(string $method): \Psr\Http\Message\RequestInterface
            {
                // TODO: Implement withMethod() method.
            }

            public function getUri(): UriInterface
            {
                // TODO: Implement getUri() method.
            }

            public function withUri(UriInterface $uri, bool $preserveHost = false): \Psr\Http\Message\RequestInterface
            {
                // TODO: Implement withUri() method.
            }

            public function getServerParams(): array
            {
                // TODO: Implement getServerParams() method.
            }

            public function getCookieParams(): array
            {
                // TODO: Implement getCookieParams() method.
            }

            public function withCookieParams(array $cookies): ServerRequestInterface
            {
                // TODO: Implement withCookieParams() method.
            }

            public function getQueryParams(): array
            {
                // TODO: Implement getQueryParams() method.
            }

            public function withQueryParams(array $query): ServerRequestInterface
            {
                // TODO: Implement withQueryParams() method.
            }

            public function getUploadedFiles(): array
            {
                // TODO: Implement getUploadedFiles() method.
            }

            public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
            {
                // TODO: Implement withUploadedFiles() method.
            }

            public function getParsedBody()
            {
                return [];
            }

            public function withParsedBody($data): ServerRequestInterface
            {
                // TODO: Implement withParsedBody() method.
            }

            public function getAttributes(): array
            {
                // TODO: Implement getAttributes() method.
            }

            /**
             * @param string $name
             * @param $default
             * @return mixed
             */
            public function getAttribute(string $name, $default = null)
            {
                // TODO: Implement getAttribute() method.
            }

            public function withAttribute(string $name, $value): ServerRequestInterface
            {
                // TODO: Implement withAttribute() method.
            }

            public function withoutAttribute(string $name): ServerRequestInterface
            {
                // TODO: Implement withoutAttribute() method.
            }
        };
    },
    'response_factory' => static function (): ResponseFactoryInterface {
        return new class implements ResponseFactoryInterface {
            public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
            {
                return new \Test\PhpDevCommunity\Michel\Core\Response\ResponseTest();
            }
        };
    },
    'container' => static function (array $definitions, array $options): ContainerInterface {

        return new class($definitions) implements ContainerInterface  {

            private array $definitions;

            public function __construct(array $definitions)
            {
                $this->definitions = $definitions;
            }

            /**
             * @param string $id
             * @return mixed
             */
            public function get(string $id)
            {
                $value = $this->definitions[$id] ?? null;
                if ($value instanceof Closure) {
                    return $value($this);
                }
                return $value;
            }

            public function has(string $id): bool
            {
                return array_key_exists($id, $this->definitions);
            }
        };
    },
    'custom_environments' => ['test'],
];
