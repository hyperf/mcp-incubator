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
use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Http\EventStream;
use Hyperf\Engine\Http\Stream;
use Hyperf\HttpMessage\Server\Response as HttpResponse;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc\ErrorResponse;
use Hyperf\Rpc\Protocol;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\Rpc\Response;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class McpHandler
{
    /**
     * @var EventStream[]
     */
    public array $connections = [];

    /**
     * @var array<string, int>
     */
    public array $fdMaps = [];

    /**
     * @var Protocol[]
     */
    protected array $protocols;

    public function __construct(
        protected RequestInterface $request,
        protected ResponseInterface $response,
        protected IdGeneratorInterface $idGenerator,
        protected ContainerInterface $container,
        protected ProtocolManager $protocolManager,
        protected LoggerInterface $logger,
    ) {
    }

    public function handler(string $path = '/messages'): void
    {
        $serverName = $this->getServerName();

        $sessionId = $this->idGenerator->generate();

        $fd = $this->getFd();

        $this->logger->debug("McpSSE Request {$serverName} {$fd} {$sessionId}");

        $eventStream = (new EventStream($this->response->getConnection()))
            ->write('event: endpoint' . PHP_EOL)
            ->write("data: {$path}?sessionId={$sessionId}" . PHP_EOL . PHP_EOL);

        $this->connections[$serverName][$fd] = $eventStream;
        $this->fdMaps[$serverName][$sessionId] = $fd;
        if (empty($this->protocols[$serverName])) {
            $this->protocols[$serverName] = new Protocol($this->container, $this->protocolManager, $serverName);
        }

        CoordinatorManager::until("fd:{$fd}")->yield();

        unset($this->connections[$serverName][$fd], $this->fdMaps[$serverName][$sessionId]);
    }

    public function message(): HttpResponse
    {
        $serverName = $this->getServerName();
        $protocol = $this->getProtocol();
        $data = $protocol->getPacker()->unpack($this->request->getBody()->getContents());
        $sessionId = $this->request->input('sessionId');

        $this->logger->debug("McpSSE Request {$serverName} {$this->getFd()} {$sessionId} {$data['method']}");

        switch ($data['method']) {
            case 'initialize':
                $result = [
                    'protocolVersion' => '2024-11-05',
                    'capabilities' => new Capabilities(
                        CollectionManager::getToolsCollection($serverName)->isNotEmpty(),
                        CollectionManager::getResourcesCollection($serverName)->isNotEmpty(),
                        CollectionManager::getPromptsCollection($serverName)->isNotEmpty(),
                    ),
                    'serverInfo' => [
                        'name' => $serverName,
                        'version' => '1.0.0',
                    ],
                ];

                $this->sendMessage($result);
                break;
            case 'tools/call':
                ['class' => $class, 'method' => $method] = McpCollector::getMethodByIndex($data['params']['name'], $serverName);
                $class = $this->container->get($class);
                $result = $class->{$method}(...$data['params']['arguments']);

                $this->sendMessage(['content' => [['type' => 'text', 'text' => (string) $result]]]);
                break;
            case 'tools/list':
                $this->sendMessage(['tools' => CollectionManager::getToolsCollection($serverName)]);
                break;
            case 'resources/list':
                $this->sendMessage(['resources' => CollectionManager::getResourcesCollection($serverName)]);
                break;
            case 'resources/read':
                /** @var Annotation\Resource $annotation */
                ['class' => $class, 'method' => $method, 'annotation' => $annotation] = McpCollector::getMethodByIndex($data['params']['uri'], $serverName);
                $class = $this->container->get($class);
                $result = $class->{$method}();

                $this->sendMessage(['content' => [['uri' => $annotation->uri, 'mimeType' => $annotation->mimeType, 'text' => (string) $result]]]);
                break;
            case 'prompts/list':
                $this->sendMessage(['prompts' => CollectionManager::getPromptsCollection($serverName)]);
                break;
            case 'prompts/get':
                /** @var Annotation\Prompt $annotation */
                ['class' => $class, 'method' => $method, 'annotation' => $annotation] = McpCollector::getMethodByIndex($data['params']['name'], $serverName);
                $class = $this->container->get($class);
                $result = $class->{$method}(...$data['params']['arguments']);

                $this->sendMessage(['messages' => [['role' => $annotation->role, 'content' => ['type' => 'text', 'text' => (string) $result]]]]);
                break;
            case 'notifications/initialized':
            default:
                break;
        }
        return (new HttpResponse())->setStatus(202)->setBody(new Stream('Accepted'));
    }

    public function sendMessage($result): void
    {
        $result = $this->getProtocol()->getDataFormatter()->formatResponse(new Response($this->getRequestId(), $result));
        $this->send($result);
    }

    public function sendErrorMessage(Throwable $throwable): void
    {
        $errorResponse = new ErrorResponse($this->getRequestId(), $throwable->getCode(), $throwable->getMessage(), $throwable);
        $result = $this->getProtocol()->getDataFormatter()->formatErrorResponse($errorResponse);
        $this->send($result);
    }

    private function send($result): void
    {
        $result = $this->getProtocol()->getPacker()->pack($result);
        $fd = $this->fdMaps[$this->getServerName()][$this->request->input('sessionId')];
        $this->connections[$this->getServerName()][$fd]->write($result);
    }

    private function getRequestId(): int
    {
        $data = $this->getProtocol()->getPacker()->unpack($this->request->getBody()->getContents());
        return $data['id'];
    }

    private function getProtocol(): Protocol
    {
        return $this->protocols[$this->getServerName()];
    }

    private function getServerName(): string
    {
        return $this->request->getAttribute(Dispatched::class)->serverName;
    }

    private function getFd(): int
    {
        return RequestContext::get()->getSwooleRequest()->fd;
    }
}
