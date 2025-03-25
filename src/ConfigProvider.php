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

use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\Mcp\Listener\RegisterProtocolListener;
use Hyperf\Rpc\IdGenerator\UniqidIdGenerator;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                IdGeneratorInterface::class => UniqidIdGenerator::class
            ],
            'listeners' => [
                RegisterProtocolListener::class
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
