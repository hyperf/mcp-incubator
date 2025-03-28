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
use Hyperf\Mcp\McpCollector;

#[Attribute(Attribute::TARGET_METHOD)]
class Resource extends McpAnnotation
{
    public function __construct(
        public string $uri,
        public string $name,
        public string $mimeType = 'text/plain',
        public string $description = '',
        public string $serverName = 'mcp-sse',
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        McpCollector::collectMethod($className, $target, $this->uri, $this);
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
