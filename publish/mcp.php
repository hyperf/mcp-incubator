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
return [
    'servers' => [
        [
            'name' => 'demo',
            'description' => 'Demo server',
            'version' => '1.0.0',
            // sse
            'sse' => [
                'server' => 'mcp-sse',
                'endpoint' => '/sse',
            ],
        ],
    ],
];
