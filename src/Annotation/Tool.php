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
use Hyperf\Di\ReflectionManager;
use Hyperf\Mcp\Collector\McpCollector;
use Hyperf\Mcp\Collector\ToolCollector;
use Hyperf\Mcp\Constants;
use InvalidArgumentException;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_METHOD)]
class Tool extends McpAnnotation
{
    public function __construct(
        public string $name,
        public string $description = '',
        public string $server = '',
        public string $serverName = Constants::DEFAULT_SERVER_NAME
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        $this->className = $className;
        $this->target = $target;
        McpCollector::collectMethod($className, $target, $this->name, $this);

        ToolCollector::set($this->server . '.' . $this->name, $this);
    }

    public function toSchema(): array
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $this->name)) {
            throw new InvalidArgumentException('Tool name must be alphanumeric and underscores.');
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'inputSchema' => $this->generateInputSchema(),
        ];
    }

    private function generateInputSchema(): array
    {
        $reflection = ReflectionManager::reflectMethod($this->className, $this->target);
        $parameters = $reflection->getParameters();
        $properties = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType()?->getName() ?? 'string'; // @phpstan-ignore method.notFound
            $type = match ($type) {
                'int' => 'integer',
                'float' => 'number',
                'bool' => 'boolean',
                default => $type,
            };
            $properties[$parameter->getName()] = [
                'type' => $type,
                'description' => self::getDescription($parameter),
            ];
        }

        $required = array_filter(
            array_map(fn (ReflectionParameter $parameter) => $parameter->isOptional() ? null : $parameter->getName(), $parameters)
        );

        return array_filter([
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
            '$schema' => 'http://json-schema.org/draft-07/schema#',
        ]);
    }
}
