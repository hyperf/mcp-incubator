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

namespace Hyperf\Mcp\Transport;

use Hyperf\Mcp\Types\Message\MessageInterface;

interface TransportInterface
{
    public function sendMessage(MessageInterface $message): void;

    public function readMessage(): MessageInterface;
}
