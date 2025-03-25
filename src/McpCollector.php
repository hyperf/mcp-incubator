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

use Hyperf\Mcp\Annotation\AbstractMcpAnnotation;
use Hyperf\Di\MetadataCollector;
use InvalidArgumentException;

class McpCollector extends MetadataCollector
{
    protected static array $container = [];

    /**
     * @param AbstractMcpAnnotation $value
     */
    public static function collectMethod(string $class, string $method, string $annotation, $value): void
    {
        if (static::$container['_name'][$value->name] ?? null) {
            throw new InvalidArgumentException("{$annotation} name {$value->name} is exist!");
        }
        static::$container[$annotation][$class][$method] = $value;
        static::$container['_name'][$value->name] = ['class' => $class, 'method' => $method];
    }

    public static function getMethodsByAnnotation(string $annotation): array
    {
        $result = [];

        foreach (static::$container[$annotation] ?? [] as $class => $metadata) {
            foreach ($metadata as $method => $value) {
                $result[] = ['class' => $class, 'method' => $method, 'annotation' => $value];
            }
        }
        return $result;
    }

    public static function getMethodByName(string $name): ?array
    {
        return static::$container['_name'][$name] ?? null;
    }

    public static function clear(?string $key = null): void
    {
    }
}
