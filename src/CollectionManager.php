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
use Hyperf\Mcp\Annotation\AbstractMcpAnnotation;
use Hyperf\Mcp\Annotation\Prompt;
use Hyperf\Mcp\Annotation\Resource;
use Hyperf\Mcp\Annotation\Tool;

class CollectionManager
{
    /**
     * @var Collection[][]
     */
    protected static array $collections = [];

    /**
     * @param class-string<AbstractMcpAnnotation> $annotation
     */
    public static function getCollection(string $serverName, string $annotation): Collection
    {
        if (isset(self::$collections[$serverName][$annotation])) {
            return self::$collections[$serverName][$annotation];
        }
        $classes = McpCollector::getMethodsByAnnotation($annotation, $serverName);

        self::$collections[$serverName][$annotation] = new Collection();
        foreach ($classes as $class) {
            /* @var array{class: string, method: string, annotation: AbstractMcpAnnotation} $class */
            self::$collections[$serverName][$annotation]->push($class['annotation']->toSchema());
        }
        return self::$collections[$serverName][$annotation];
    }

    public static function getToolsCollection(string $serverName): Collection
    {
        return self::getCollection($serverName, Tool::class);
    }

    public static function getResourcesCollection(string $serverName): Collection
    {
        return self::getCollection($serverName, Resource::class);
    }

    public static function getPromptsCollection(string $serverName): Collection
    {
        return self::getCollection($serverName, Prompt::class);
    }
}
