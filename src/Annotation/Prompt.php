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
class Prompt extends AbstractMcpAnnotation
{
    public function __construct(public string $name, public string $description = '', public string $role = 'user', public string $serverName = 'mcp-sse')
    {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        McpCollector::collectMethod($className, $target, $this->name, $this);
    }
}
