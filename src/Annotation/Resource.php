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

use Attribute;
use Hyperf\Mcp\Collector\McpCollector;
use Hyperf\Mcp\Collector\ResourceCollector;
use Hyperf\Mcp\Constants;

#[Attribute(Attribute::TARGET_METHOD)]
class Resource extends McpAnnotation
{
    public function __construct(
        public string $uri,
        public string $name,
        public string $description = '',
        public string $mimeType = 'text/plain',
        public string $serverName = Constants::DEFAULT_SERVER_NAME,
        public string $server = '',
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        McpCollector::collectMethod($className, $target, $this->uri, $this);

        ResourceCollector::set($this->server . '.' . $this->uri, $this);
    }

    public function toSchema(): array
    {
        return [
            'name' => $this->name,
            'uri' => $this->uri,
            'mimeType' => $this->mimeType,
            'description' => $this->description,
        ];
    }
}
