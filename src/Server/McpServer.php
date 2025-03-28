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

namespace Hyperf\Mcp\Server;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\HttpServer\Server;
use Hyperf\Mcp\Exception\Handler\McpSseExceptionHandler;

class McpServer extends Server implements OnCloseInterface
{
    protected string $version = '1.0.0';

    public function onClose($server, int $fd, int $reactorId): void
    {
        CoordinatorManager::until("fd:{$fd}")->resume();
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    protected function getDefaultExceptionHandler(): array
    {
        return [
            McpSseExceptionHandler::class,
        ];
    }
}
