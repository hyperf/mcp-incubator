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

    protected static bool $init = false;

    public static function collectMethod(string $class, string $method, string $index, McpAnnotation $value): void
    {
        $annotation = $value::class;
        if (static::$container[$value->serverName]['_index'][$index] ?? null) {
            throw new InvalidArgumentException("{$annotation} index {$index} is exist on {$value->serverName} !");
        }
        static::$container[$value->serverName][$annotation][$class][$method] = $value;
        static::$container[$value->serverName]['_index'][$index] = ['class' => $class, 'method' => $method, 'annotation' => $value];
    }

    /**
     * @param class-string<McpAnnotation> $annotation
     */
    public static function getMethodsByAnnotation(string $annotation, string $serverName = Constants::DEFAULT_SERVER_NAME): array
    {
        $result = [];
        foreach (static::$container[$serverName][$annotation] ?? [] as $class => $metadata) {
            foreach ($metadata as $method => $value) {
                $result[] = ['class' => $class, 'method' => $method, 'annotation' => $value];
            }
        }
        return $result;
    }

    public static function getMethodByIndex(string $index, string $serverName = Constants::DEFAULT_SERVER_NAME): ?array
    {
        return static::$container[$serverName]['_index'][$index] ?? null;
    }

    public static function clear(?string $key = null): void
    {
        if (! self::$init) {
            self::$init = true;
            static::$container = [];
        }
    }
}
