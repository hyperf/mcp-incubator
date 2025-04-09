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

use Hyperf\Mcp\Collector\McpCollector;
use Hyperf\Mcp\Collector\PromptCollector;
use Hyperf\Mcp\Collector\ResourceCollector;
use Hyperf\Mcp\Collector\ToolCollector;
use Hyperf\Mcp\Contract\IdGeneratorInterface;
use Hyperf\Mcp\IdGenerator\UniqidIdGenerator;
use Hyperf\Mcp\Server\Listener\RegisterCommandListener;
use Hyperf\Mcp\Server\Listener\RegisterSseRouterListener;

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                IdGeneratorInterface::class => UniqidIdGenerator::class,
            ],
            'listeners' => [
                RegisterCommandListener::class,
                RegisterSseRouterListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'collectors' => [
                        McpCollector::class,
                        ToolCollector::class,
                        ResourceCollector::class,
                        PromptCollector::class,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of hyperf/mcp.',
                    'source' => __DIR__ . '/../publish/mcp.php',
                    'destination' => BASE_PATH . '/config/autoload/mcp.php',
                ],
            ],
        ];
    }
}
