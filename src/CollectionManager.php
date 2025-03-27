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

use Hyperf\Collection\Collection;
use Hyperf\Di\ReflectionManager;
use Hyperf\Mcp\Annotation\Description;
use Hyperf\Mcp\Annotation\Prompt;
use Hyperf\Mcp\Annotation\Resource;
use Hyperf\Mcp\Annotation\Tool;
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

    public static function getResourcesCollection(string $serverName): Collection
    {
        if (isset(self::$collections[$serverName]['resources'])) {
            return self::$collections[$serverName]['resources'];
        }
        $classes = McpCollector::getMethodsByAnnotation(Resource::class, $serverName);

        self::$collections[$serverName]['resources'] = new Collection();
        foreach ($classes as $class) {
            /** @var array{class: string, method: string, annotation: resource} $class */
            $annotation = $class['annotation'];
            self::$collections[$serverName]['resources']->push([
                'name' => $annotation->name,
                'uri' => $annotation->uri,
                'mimeType' => $annotation->mimeType,
                'description' => $annotation->description,
            ]);
        }
        return self::$collections[$serverName]['resources'];
    }

    public static function getPromptsCollection(string $serverName): Collection
    {
        if (isset(self::$collections[$serverName]['prompts'])) {
            return self::$collections[$serverName]['prompts'];
        }
        $classes = McpCollector::getMethodsByAnnotation(Prompt::class, $serverName);

        self::$collections[$serverName]['prompts'] = new Collection();
        foreach ($classes as $class) {
            /** @var array{class: string, method: string, annotation: Prompt} $class */
            $annotation = $class['annotation'];
            self::$collections[$serverName]['prompts']->push([
                'name' => $annotation->name,
                'description' => $annotation->description,
                'arguments' => self::generateArguments($class['class'], $class['method']),
            ]);
        }
        return self::$collections[$serverName]['prompts'];
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
            $properties[$parameter->getName()] = ['type' => $type, 'description' => self::getDescription($parameter)];
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

    private static function generateArguments(mixed $class, mixed $method): array
    {
        $reflection = ReflectionManager::reflectMethod($class, $method);
        $parameters = $reflection->getParameters();
        $arguments = [];
        foreach ($parameters as $parameter) {
            $arguments[] = [
                'name' => $parameter->getName(),
                'description' => self::getDescription($parameter),
                'required' => ! $parameter->isOptional(),
            ];
        }
        return $arguments;
    }

    private static function getDescription(ReflectionParameter $parameter): string
    {
        foreach ($parameter->getAttributes() as $attribute) {
            if ($attribute->getName() === Description::class) {
                return $attribute->newInstance()->description;
            }
        }
        return '';
    }
}
