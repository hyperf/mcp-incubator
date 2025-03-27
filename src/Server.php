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

use Hyperf\Context\RequestContext;
use Hyperf\Mcp\Contract\ServerInterface;

class Server implements ServerInterface
{
    public function __construct(protected McpHandler $handler)
    {
    }

    public function process(): void
    {
        $request = RequestContext::get();
        $path = $request->getUri()->getPath();

        if ($request->getMethod() === 'GET') {
            $this->handler->handler($path);
            return;
        }

        $this->handler->message();
    }
}
