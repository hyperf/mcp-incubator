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

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Mcp\Constants;
use ReflectionParameter;

abstract class McpAnnotation extends AbstractAnnotation
{
    public string $name;

    public string $description = '';

    public string $serverName = Constants::DEFAULT_SERVER_NAME;

    public string $className;

    public string $target;

    abstract public function toSchema(): array;

    protected static function getDescription(ReflectionParameter $parameter): string
    {
        foreach ($parameter->getAttributes() as $attribute) {
            if ($attribute->getName() === Description::class) {
                return $attribute->newInstance()->description;
            }
        }

        return '';
    }
}
