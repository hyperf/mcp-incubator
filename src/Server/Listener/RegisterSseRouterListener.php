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

namespace Hyperf\Mcp\Server\Listener;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\RequestContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use Hyperf\Mcp\Server\McpHandler;
use Hyperf\Mcp\Server\McpServer;
use Hyperf\Mcp\Server\Transport\SseTransport;

class RegisterSseRouterListener implements ListenerInterface
{
    public function __construct(
        protected DispatcherFactory $dispatcherFactory, // Don't remove this line
        protected ConfigInterface $config,
        protected SseTransport $transport,
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        foreach ($this->config->get('server.servers', []) as $name => $server) {
            $serverName = $server['name'] ?? $name;
            $route = $server['options']['route'] ?? '/';

            foreach ($server['callbacks'] ?? [] as $event => $callback) {
                [$class] = $callback;
                if (is_a($class, McpServer::class, true)) {
                    $this->registerRouter($serverName, $route);
                    break;
                }
            }
        }
    }

    protected function registerRouter(string $serverName, string $path): void
    {
        $handler = new McpHandler($serverName, ApplicationContext::getContainer());
        Router::addServer($serverName, function () use ($path, $handler) {
            Router::addRoute(['GET', 'POST'], $path, function () use ($path, $handler) {
                match (RequestContext::get()->getMethod()) {
                    'GET' => $this->transport->register($path),
                    'POST' => $this->transport->sendMessage($handler->handle($this->transport->readMessage())),
                    default => null,
                };
            });
        });
    }
}
