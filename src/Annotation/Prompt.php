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
use Hyperf\Mcp\Constants;
use Hyperf\Mcp\McpCollector;

#[Attribute(Attribute::TARGET_METHOD)]
class Prompt extends McpAnnotation
{
    public function __construct(
        public string $name,
        public string $description = '',
        public string $role = 'user',
        public string $serverName = Constants::DEFAULT_SERVER_NAME
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        $this->className = $className;
        $this->target = $target;
        McpCollector::collectMethod($className, $target, $this->name, $this);
    }

    public function toSchema(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'arguments' => $this->generateArguments(),
        ];
    }

    private function generateArguments(): array
    {
        $reflection = ReflectionManager::reflectMethod($this->className, $this->target);
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
}
