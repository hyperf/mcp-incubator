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

use JsonSerializable;
use stdClass;

class Capabilities implements JsonSerializable
{
    public function __construct(
        protected ?bool $hasTools = null,
        protected ?bool $hasResources = null,
        protected ?bool $hasPrompts = null,
    ) {
    }

    public function jsonSerialize(): stdClass
    {
        $capabilities = new stdClass();
        if ($this->hasTools) {
            $capabilities->tools = new stdClass();
        }
        if ($this->hasResources) {
            $capabilities->resources = new stdClass();
        }
        if ($this->hasPrompts) {
            $capabilities->prompts = new stdClass();
        }
        return $capabilities;
    }
}
