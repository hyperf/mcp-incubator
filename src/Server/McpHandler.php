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

namespace Hyperf\Mcp\Server;

use Hyperf\Mcp\Annotation;
use Hyperf\Mcp\Capabilities;
use Hyperf\Mcp\McpCollector;
use Hyperf\Mcp\TypeCollection;
use Hyperf\Mcp\Types\Message\Notification;
use Hyperf\Mcp\Types\Message\Request;
use Hyperf\Mcp\Types\Message\Response;
use Psr\Container\ContainerInterface;

class McpHandler
{
    public function __construct(protected string $serverName, protected ContainerInterface $container)
    {
    }

    public function handle(Notification|Request $request): ?Response
    {
        $serverName = $this->serverName;
        switch ($request->method) {
            case 'initialize':
                $result = [
                    'protocolVersion' => '2024-11-05',
                    'capabilities' => new Capabilities(
                        TypeCollection::getTools($serverName)->isNotEmpty(),
                        TypeCollection::getResources($serverName)->isNotEmpty(),
                        TypeCollection::getPrompts($serverName)->isNotEmpty(),
                    ),
                    'serverInfo' => [
                        'name' => $serverName,
                        'version' => '1.0.0',
                    ],
                ];
                break;
            case 'tools/call':
                ['class' => $class, 'method' => $method] = McpCollector::getMethodByIndex($request->params['name'], $serverName);
                $class = $this->container->get($class);
                $result = $class->{$method}(...$request->params['arguments']);

                $result = ['content' => [['type' => 'text', 'text' => $result]]];
                break;
            case 'tools/list':
                $result = ['tools' => TypeCollection::getTools($serverName)];
                break;
            case 'resources/list':
                $result = ['resources' => TypeCollection::getResources($serverName)];
                break;
            case 'resources/read':
                /** @var Annotation\Resource $annotation */
                ['class' => $class, 'method' => $method, 'annotation' => $annotation] = McpCollector::getMethodByIndex($request->params['uri'], $serverName);
                $class = $this->container->get($class);
                $result = $class->{$method}();

                $result = ['content' => [['uri' => $annotation->uri, 'mimeType' => $annotation->mimeType, 'text' => $result]]];
                break;
            case 'prompts/list':
                $result = ['prompts' => TypeCollection::getPrompts($serverName)];
                break;
            case 'prompts/get':
                /** @var Annotation\Prompt $annotation */
                ['class' => $class, 'method' => $method, 'annotation' => $annotation] = McpCollector::getMethodByIndex($request->params['name'], $serverName);
                $class = $this->container->get($class);
                $result = $class->{$method}(...$request->params['arguments']);

                $result = ['messages' => [['role' => $annotation->role, 'content' => ['type' => 'text', 'text' => $result]]]];
                break;
            case 'notifications/initialized':
            default:
                return null;
        }

        return new Response($request->id, $request->jsonrpc, $result);
    }
}
