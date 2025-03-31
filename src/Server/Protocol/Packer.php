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

namespace Hyperf\Mcp\Server\Protocol;

use Hyperf\Codec\Json;
use Hyperf\Contract\PackerInterface;

class Packer implements PackerInterface
{
    public function pack(mixed $data): string
    {
        return "event: message\ndata: " . Json::encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;
    }

    public function unpack(string $data): mixed
    {
        return Json::decode($data);
    }
}
