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

use Hyperf\Mcp\Annotation\Tool;
use Hyperf\Collection\Collection;
use Hyperf\Di\ReflectionManager;
use ReflectionParameter;

class CollectionManager
{
    /**
     * @var Collection[][]
     */
    protected static array $collections = [];

    public static function getToolsCollection(string $serverName): Collection
    {
        if (isset(self::$collections[$serverName]['tools'])) {
            return self::$collections[$serverName]['tools'];
        }
        $classes = McpCollector::getMethodsByAnnotation(Tool::class, $serverName);

        self::$collections[$serverName]['tools'] = new Collection();
        foreach ($classes as $class) {
            /** @var array{class: string, method: string, annotation: Tool} $class */
            $annotation = $class['annotation'];
            self::$collections[$serverName]['tools']->push([
                'name' => $annotation->name,
                'description' => $annotation->description,
                'inputSchema' => self::generateInputSchema($class['class'], $class['method']),
            ]);
        }
        return self::$collections[$serverName]['tools'];
    }

    private static function generateInputSchema(string $class, string $method): array
    {
        $reflection = ReflectionManager::reflectMethod($class, $method);
        $parameters = $reflection->getParameters();
        $properties = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType()?->getName();
            $type = match ($type) {
                'int' => 'integer',
                'float' => 'number',
                'bool' => 'boolean',
                default => $type,
            };
            $properties[$parameter->getName()] = ['type' => $type];
        }
        $required = array_filter(array_map(fn (ReflectionParameter $parameter) => $parameter->isOptional() ? null : $parameter->getName(), $parameters));
        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
            '$schema' => 'http://json-schema.org/draft-07/schema#',
        ];
    }
}
