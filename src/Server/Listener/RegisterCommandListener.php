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

namespace Hyperf\Mcp\Server\Listener;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Mcp\Server\Annotation\Server;
use Hyperf\Mcp\Server\McpHandler;
use Hyperf\Mcp\Server\Protocol\Packer;
use Hyperf\Mcp\Server\Transport\StdioTransport;
use Psr\Container\ContainerInterface;

class RegisterCommandListener implements ListenerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var array<array-key, Server> $classes */
        $classes = AnnotationCollector::getClassesByAnnotation(Server::class);

        foreach ($classes as $annotation) {
            if (! $annotation->signature) {
                continue;
            }

            $asCommand = new class($this->container, $annotation) extends Command {
                protected bool $coroutine = false;

                protected string $serverName;

                public function __construct(protected ContainerInterface $container, Server $annotation)
                {
                    $this->signature = $annotation->signature;
                    $this->description = $annotation->description;
                    $this->serverName = $annotation->name;
                    parent::__construct();
                }

                public function handle(): void
                {
                    $transport = new StdioTransport(
                        $this->input,
                        $this->output,
                        $this->container->get(Packer::class)
                    );
                    $handler = new McpHandler($this->serverName, $this->container);

                    while (true) { // @phpstan-ignore while.alwaysTrue
                        $request = $transport->readMessage();
                        if ($response = $handler->handle($request)) {
                            $transport->sendMessage($response);
                        }
                    }
                }
            };

            $hash = spl_object_hash($asCommand);
            $this->container->set($hash, $asCommand); // @phpstan-ignore method.notFound
            $this->appendConfig('commands', $hash);
        }
    }

    private function appendConfig(string $key, mixed $configValues): void
    {
        $configs = $this->config->get($key, []);
        $configs[] = $configValues;
        $this->config->set($key, $configs);
    }
}
