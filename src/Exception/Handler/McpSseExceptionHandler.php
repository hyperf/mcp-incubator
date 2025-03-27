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

namespace Hyperf\Mcp\Exception\Handler;

use Hyperf\Engine\Http\Stream;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Server\Response as HttpResponse;
use Hyperf\Mcp\McpHandler;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class McpSseExceptionHandler extends ExceptionHandler
{
    public function __construct(protected McpHandler $handler)
    {
    }

    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        $this->handler->sendErrorMessage($throwable);
        return (new HttpResponse())->setStatus(202)->setBody(new Stream('Accepted'));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
