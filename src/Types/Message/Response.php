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

namespace Hyperf\Mcp\Types\Message;

class Response implements MessageInterface
{
    public function __construct(public int $id, public string $jsonrpc, public ?array $result = null)
    {
    }
}
