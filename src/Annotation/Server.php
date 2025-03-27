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

namespace Hyperf\Mcp\Annotation;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;

use function Hyperf\Support\make;

class Server extends AbstractAnnotation
{
    public function __construct(
        public string $name = 'test',
        public string $description = '',
        public string $serverName = 'mcp-sse',
        public ?string $asCommand = null,
    ) {
    }

    public function collectClass(string $className): void
    {
        ApplicationContext::getContainer()->get(DispatcherFactory::class); // Don't remove this line.

        Router::addServer($this->serverName, function () use ($className) {
            Router::addRoute(['GET', 'POST'], $this->name, [make($className), 'process']);
        });
    }
}
