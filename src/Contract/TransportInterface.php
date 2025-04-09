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

namespace Hyperf\Mcp\Contract;

use Hyperf\Mcp\Types\Message\MessageInterface;

interface TransportInterface
{
    public function sendMessage(MessageInterface $message): void;

    public function readMessage(): MessageInterface;

    public function setOnMessage(callable $callback): void;

    public function setOnClose(callable $callback): void;

    public function setOnError(callable $callback): void;
}
