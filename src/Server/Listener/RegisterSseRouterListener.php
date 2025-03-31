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

use Hyperf\Context\RequestContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use Hyperf\Mcp\McpHandler;
use Hyperf\Mcp\Server\McpServer;

class RegisterSseRouterListener implements ListenerInterface
{
    public function __construct(
        protected DispatcherFactory $dispatcherFactory, // Don't remove this line
        protected ConfigInterface $config,
        protected McpHandler $mcpHandler
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
            $path = $server['options']['mcp_path'] ?? '/';

            foreach ($server['callbacks'] ?? [] as $event => $callback) {
                [$class] = $callback;
                if (is_a($class, McpServer::class, true)) {
                    $this->registerRouter($serverName, $path);
                    break;
                }
            }
        }
    }

    protected function registerRouter(string $serverName, string $path): void
    {
        Router::addServer($serverName, function () use ($path) {
            Router::addRoute(['GET', 'POST'], $path, function () use ($path) {
                match (RequestContext::get()->getMethod()) {
                    'GET' => $this->mcpHandler->handle($path),
                    'POST' => $this->mcpHandler->process(),
                    default => null,
                };
            });
        });
    }
}
