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

namespace Hyperf\Mcp\Collector;

use Hyperf\Di\MetadataCollector;

class ToolCollector extends MetadataCollector
{
    /**
     * @var array<string, array<string, Tool>>
     */
    protected static array $container = [];
}
