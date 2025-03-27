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
use Hyperf\Mcp\Annotation\McpAnnotation;
use InvalidArgumentException;

class McpCollector extends MetadataCollector
{
    protected static array $container = [];

    public static function collectMethod(string $class, string $method, string $index, McpAnnotation $value): void
    {
        $annotation = $value::class;
        if (static::$container[$value->server]['_index'][$index] ?? null) {
            throw new InvalidArgumentException("{$annotation} index {$index} is exist!");
        }
        static::$container[$value->server][$annotation][$class][$method] = $value;
        static::$container[$value->server]['_index'][$index] = ['class' => $class, 'method' => $method, 'annotation' => $value];
    }

    /**
     * @param class-string<McpAnnotation> $annotation
     */
    public static function getMethodsByAnnotation(string $annotation, string $server = 'default'): array
    {
        $result = [];
        foreach (static::$container[$server][$annotation] ?? [] as $class => $metadata) {
            foreach ($metadata as $method => $value) {
                $result[] = ['class' => $class, 'method' => $method, 'annotation' => $value];
            }
        }
        return $result;
    }

    public static function getMethodByIndex(string $index, string $server = 'default'): ?array
    {
        return static::$container[$server]['_index'][$index] ?? null;
    }
}
