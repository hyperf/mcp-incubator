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

use Hyperf\Mcp\Types\Message\Notification;
use Hyperf\Mcp\Types\Message\Request;
use Hyperf\Mcp\Types\Message\Response;
use RuntimeException;

class Server
{
    protected array $toolHandlers = [];

    protected array $toolDefinitions = [];

    protected array $resourceHandlers = [];

    protected array $resourceDefinitions = [];

    protected array $promptHandlers = [];

    protected array $promptDefinitions = [];

    public function __construct(
        public readonly array $serverInfo = [],
    ) {
    }

    public function handleRequest(Notification|Request $request): Response
    {
        switch ($request->method) {
            case 'initialize':
                $result = $this->handleInitialize($request);
                break;
            case 'tools/call':
                $result = $this->handleCallTool($request->method, $request->params);
                if (is_string($result)) {
                    $result = ['content' => [['type' => 'text', 'text' => $result]]];
                }
                break;
            case 'tools/list':
                $result = $this->handleListTools();
                break;
            case 'resources/read':
                $result = $this->handleReadResource($request->params['uri'], $request->params);
                break;
            case 'resources/list':
                $result = $this->handleListResources();
                break;
            case 'prompts/get':
                $result = $this->handleGetPrompt($request->method, $request->params);
                break;
            case 'prompts/list':
                $result = $this->handleListPrompts();
                break;
            default:
                throw new RuntimeException("Unknown method: {$request->method}");
        }

        return new Response(
            $request->id,
            $request->jsonrpc,
            $result
        );
    }

    public function registerToolHandler(string $name, callable $handler, array $definition = []): void
    {
        $this->toolHandlers[$name] = $handler;
        $this->toolDefinitions[$name] = $definition;
    }

    public function handleCallTool(string $name, array $params): mixed
    {
        if (! isset($this->toolHandlers[$name])) {
            throw new RuntimeException("Tool handler not found: {$name}");
        }

        return ($this->toolHandlers[$name])(...$params);
    }

    public function handleListTools(): array
    {
        return ['tools' => $this->toolDefinitions];
    }

    public function registerResourceHandler(string $uri, callable $handler, array $definition = []): void
    {
        $this->toolHandlers[$uri] = $handler;
        $this->toolDefinitions[$uri] = $definition;
    }

    public function handleReadResource(string $uri, array $params): mixed
    {
        if (! isset($this->resourceHandlers[$uri])) {
            throw new RuntimeException("Resource handler not found: {$uri}");
        }

        return ($this->resourceHandlers[$uri])(...$params);
    }

    public function handleListResources(): array
    {
        return ['resources' => $this->resourceDefinitions];
    }

    public function registerPromptHandler(string $name, callable $handler, array $definition = []): void
    {
        $this->promptHandlers[$name] = $handler;
        $this->promptDefinitions[$name] = $definition;
    }

    public function handleGetPrompt(string $name, array $params): mixed
    {
        if (! isset($this->promptHandlers[$name])) {
            throw new RuntimeException("Prompt handler not found: {$name}");
        }

        return ($this->promptHandlers[$name])(...$params);
    }

    public function handleListPrompts(): array
    {
        return ['prompts' => $this->promptDefinitions];
    }

    protected function handleInitialize(Notification|Request $request): array
    {
        return [
            'protocolVersion' => '2024-11-05',
            'capabilities' => new Capabilities(
                ! empty($this->toolHandlers),
                ! empty($this->resourceHandlers),
                ! empty($this->promptHandlers),
            ),
            'serverInfo' => $this->serverInfo,
        ];
    }
}
