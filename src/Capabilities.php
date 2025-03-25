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
use JsonSerializable;
use stdClass;

class Capabilities implements JsonSerializable
{
    public function __construct(protected ?Collection $tools = null)
    {
    }

    public function jsonSerialize(): mixed
    {
        $capabilities = new stdClass();
        if ($this->tools && $this->tools->isNotEmpty()) {
            $capabilities->tools = new stdClass();
        }
        return $capabilities;
    }
}
