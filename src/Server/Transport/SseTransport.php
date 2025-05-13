<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Mcp\Server\Transport;

use Hyperf\Context\RequestContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Http\EventStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Mcp\Contract\IdGeneratorInterface;
use Hyperf\Mcp\Contract\TransportInterface;
use Hyperf\Mcp\Server\Protocol\Packer;
use Hyperf\Mcp\Types\Message\MessageInterface;
use Hyperf\Mcp\Types\Message\Notification;
use Hyperf\Mcp\Types\Message\Request;

class SseTransport implements TransportInterface
{
    /**
     * @var EventStream[]
     */
    public array $connections = [];

    /**
     * @var array<string, array<string, int>>
     */
    public array $fdMaps = [];

    public function __construct(
        protected RequestInterface $request,
        protected ResponseInterface $response,
        protected Packer $packer,
        protected IdGeneratorInterface $idGenerator,
        protected StdoutLoggerInterface $logger,
    ) {
    }

    public function sendMessage(?MessageInterface $message): void
    {
        if (! $message) {
            return;
        }
        $result = $this->packer->pack($message);
        $fd = $this->fdMaps[$this->getServerName()][$this->request->input('sessionId')];
        $this->connections[$this->getServerName()][$fd]->write("event: message\ndata: {$result}\n\n");
    }

    protected function getBodyContent(): string
    {
        // swoole header头中包含 Upgrade: h2c，会导致swoole解析body为空
        // issue：https://github.com/swoole/swoole-src/issues/5766
        $rawBody = RequestContext::get()->getSwooleRequest()->getData();
        [$_, $body] = explode("\r\n\r\n", $rawBody, 2);

        return $body;
        // return $this->request->getBody()->getContents();
    }

    public function readMessage(): Notification|Request
    {
        $message = $this->packer->unpack($this->getBodyContent());

        if (!isset($message['id'])) {
            return new Notification(...$message);
        }
        return new Request(...$message);
    }

    public function register(string $path): void
    {
        $serverName = $this->getServerName();
        $sessionId = $this->idGenerator->generate();
        $fd = $this->getFd();

        $this->logger->debug("McpSSE Request {$serverName} {$fd} {$sessionId}");

        $eventStream = (new EventStream($this->response->getConnection())) // @phpstan-ignore method.notFound
            ->write('event: endpoint' . PHP_EOL)
            ->write("data: {$path}?sessionId={$sessionId}" . PHP_EOL . PHP_EOL);

        $this->connections[$serverName][$fd] = $eventStream;
        $this->fdMaps[$serverName][$sessionId] = $fd;

        CoordinatorManager::until("fd:{$fd}")->yield();

        unset($this->connections[$serverName][$fd], $this->fdMaps[$serverName][$sessionId]);
    }

    public function getRequestId(): mixed
    {
        $data = $this->packer->unpack($this->getBodyContent());
        return $data['id'];
    }

    private function getFd(): int
    {
        return RequestContext::get()->getSwooleRequest()->fd; // @phpstan-ignore method.notFound
    }

    private function getServerName(): string
    {
        return $this->request->getAttribute(Dispatched::class)->serverName;
    }
}
