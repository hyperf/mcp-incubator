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

namespace Hyperf\Mcp;

use Hyperf\Mcp\Collector\PromptCollector;
use Hyperf\Mcp\Collector\ResourceCollector;
use Hyperf\Mcp\Collector\ToolCollector;
use InvalidArgumentException;

use function Hyperf\Tappable\tap;

class ServerRegistry
{
    /**
     * @var array<string, Server>
     */
    protected static array $servers = [];

    public static function register(string $name, Server $server): void
    {
        self::$servers[$name] = tap($server, function (Server $server) use ($name) {
            // Register Tool Handlers
            foreach (ToolCollector::get($name) as $tool) {
                $server->registerToolHandler(
                    $tool->name,
                    [$tool->className, $tool->target],
                    $tool->toSchema()
                );
            }

            // Register Resource Handlers
            foreach (ResourceCollector::get($name) as $resource) {
                $server->registerResourceHandler(
                    $resource->name,
                    [$resource->className, $resource->target],
                    $resource->toSchema()
                );
            }

            // Register Prompt Handlers
            foreach (PromptCollector::get($name) as $prompt) {
                $server->registerPromptHandler(
                    $prompt->name,
                    [$prompt->className, $prompt->target],
                    $prompt->toSchema()
                );
            }
        });
    }

    public static function get(string $name): ?Server
    {
        if (! array_key_exists($name, self::$servers)) {
            throw new InvalidArgumentException("Server not found: {$name}");
        }

        return self::$servers[$name];
    }
}
