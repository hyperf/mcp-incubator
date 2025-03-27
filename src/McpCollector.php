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

use Hyperf\Di\MetadataCollector;
use Hyperf\Mcp\Annotation\AbstractMcpAnnotation;
use InvalidArgumentException;

class McpCollector extends MetadataCollector
{
    protected static array $container = [];

    public static function collectMethod(string $class, string $method, string $index, AbstractMcpAnnotation $value): void
    {
        $annotation = $value::class;
        if (static::$container[$value->serverName]['_index'][$index] ?? null) {
            throw new InvalidArgumentException("{$annotation} index {$index} is exist!");
        }
        static::$container[$value->serverName][$annotation][$class][$method] = $value;
        static::$container[$value->serverName]['_index'][$index] = ['class' => $class, 'method' => $method, 'annotation' => $value];
    }

    /**
     * @param class-string<AbstractMcpAnnotation> $annotation
     */
    public static function getMethodsByAnnotation(string $annotation, string $serverName = 'mcp-sse'): array
    {
        $result = [];
        foreach (static::$container[$serverName][$annotation] ?? [] as $class => $metadata) {
            foreach ($metadata as $method => $value) {
                $result[] = ['class' => $class, 'method' => $method, 'annotation' => $value];
            }
        }
        return $result;
    }

    public static function getMethodByIndex(string $index, string $serverName = 'mcp-sse'): ?array
    {
        return static::$container[$serverName]['_index'][$index] ?? null;
    }
}
