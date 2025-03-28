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
use Hyperf\Mcp\IdGenerator\UniqidIdGenerator;
use Hyperf\Mcp\Listener\RegisterSseRouterListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                IdGeneratorInterface::class => UniqidIdGenerator::class,
            ],
            'listeners' => [
                RegisterSseRouterListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'collectors' => [
                        McpCollector::class,
                    ],
                ],
            ],
        ];
    }
}
