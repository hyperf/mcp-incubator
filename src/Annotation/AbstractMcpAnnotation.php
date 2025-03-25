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
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Mcp\McpCollector;

#[Attribute(Attribute::TARGET_METHOD)]
abstract class AbstractMcpAnnotation extends AbstractAnnotation
{
    public function __construct(public string $name, public string $description = '', public string $serverName = 'mcp-http')
    {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        McpCollector::collectMethod($className, $target, static::class, $this);
    }
}
