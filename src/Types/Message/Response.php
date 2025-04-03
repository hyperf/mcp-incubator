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

use JsonSerializable;

class Response implements MessageInterface, JsonSerializable
{
    public function __construct(public int $id, public string $jsonrpc, public array $result)
    {
    }

    public function jsonSerialize(): mixed
    {
        if (
            isset($this->result['content'])
        ) {
            $this->result['content'] = array_map(function ($item) {
                if (isset($item['text'], $item['type']) && $item['type'] === 'text') {
                    $item['text'] = json_encode($item['text'], JSON_UNESCAPED_UNICODE);
                }

                return $item;
            }, $this->result['content']);
        }

        return [
            'id' => $this->id,
            'jsonrpc' => $this->jsonrpc,
            'result' => $this->result,
        ];
    }
}
