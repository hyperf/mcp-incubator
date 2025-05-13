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

use Hyperf\Mcp\Contract\IdGeneratorInterface;
use Hyperf\Mcp\IdGenerator\SessionIdGenerator;
use Hyperf\Mcp\Server\Listener\RegisterCommandListener;
use Hyperf\Mcp\Server\Listener\RegisterSseRouterListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                IdGeneratorInterface::class => SessionIdGenerator::class,
            ],
            'listeners'    => [
                RegisterCommandListener::class,
                RegisterSseRouterListener::class,
            ],
            'annotations'  => [
                'scan' => [
                    'collectors' => [
                        McpCollector::class,
                    ],
                ],
            ],
        ];
    }
}
